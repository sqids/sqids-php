<?php

/**
 * Copyright (c) Sqids maintainers.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/sqids/sqids-php
 */

namespace Sqids;

use Sqids\Math\BCMath;
use Sqids\Math\Gmp;
use Sqids\Math\MathInterface;
use InvalidArgumentException;
use RuntimeException;

class Sqids implements SqidsInterface
{
    final public const DEFAULT_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    final public const DEFAULT_MIN_LENGTH = 0;

    protected MathInterface $math;

    /** @throws \InvalidArgumentException */
    public function __construct(
        protected string $alphabet = self::DEFAULT_ALPHABET,
        protected int $minLength = self::DEFAULT_MIN_LENGTH,
        protected array|null $blocklist = null,
    ) {
        $this->math = $this->getMathExtension();

        if ($alphabet == '') {
            $alphabet = self::DEFAULT_ALPHABET;
        }

        if (!isset($blocklist)) {
            $blocklist = json_decode(file_get_contents(__DIR__ . '/blocklist.json'), false, 512, JSON_THROW_ON_ERROR);
        }

        $alphabet = mb_convert_encoding($alphabet, 'UTF-8', mb_detect_encoding($alphabet));

        if (mb_strlen($alphabet) < 5) {
            throw new InvalidArgumentException('Alphabet length must be at least 5');
        }

        if (count(array_unique(str_split($alphabet))) !== mb_strlen($alphabet)) {
            throw new InvalidArgumentException('Alphabet must contain unique characters');
        }

        if (
            !is_int($minLength) ||
            $minLength < self::minValue() ||
            $minLength > mb_strlen($alphabet)
        ) {
            throw new InvalidArgumentException(
                'Minimum length has to be between ' . self::minValue() . ' and ' . mb_strlen($alphabet)
            );
        }

        $filteredBlocklist = [];
        $alphabetChars = str_split($alphabet);
        foreach ((array) $blocklist as $word) {
            if (mb_strlen((string) $word) >= 3) {
                $wordChars = str_split((string) $word);
                $intersection = array_filter($wordChars, fn ($c) => in_array($c, $alphabetChars));
                if (count($intersection) == count($wordChars)) {
                    $filteredBlocklist[] = strtolower((string) $word);
                }
            }
        }

        $this->alphabet = $this->shuffle($alphabet);
        $this->minLength = $minLength;
        $this->blocklist = $filteredBlocklist;
    }

    /**
     * Encodes an array of unsigned integers into an ID
     *
     * These are the cases where encoding might fail:
     * - One of the numbers passed is smaller than `minValue()` or greater than `maxValue()`
     * - A partition number is incremented so much that it becomes greater than `maxValue()`
     *
     * @param array<int> $numbers Non-negative integers to encode into an ID
     * @return string Generated ID
     */
    public function encode(array $numbers): string
    {
        if (count($numbers) == 0) {
            return '';
        }

        $inRangeNumbers = array_filter($numbers, fn ($n) => $n >= self::minValue() && $n <= self::maxValue());
        if (count($inRangeNumbers) != count($numbers)) {
            throw new \InvalidArgumentException(
                'Encoding supports numbers between ' . self::minValue() . ' and ' . self::maxValue()
            );
        }

        return $this->encodeNumbers($numbers, false);
    }

    /**
     * Internal function that encodes an array of unsigned integers into an ID
     *
     * @param array<int> $numbers Non-negative integers to encode into an ID
     * @param bool $partitioned If true, the first number is always a throwaway number (used either for blocklist or padding)
     * @return string Generated ID
     */
    protected function encodeNumbers(array $numbers, bool $partitioned = false): string
    {
        $offset = count($numbers);
        foreach ($numbers as $i => $v) {
            $offset += mb_ord($this->alphabet[$v % mb_strlen($this->alphabet)]) + $i;
        }
        $offset %= mb_strlen($this->alphabet);

        $alphabet = mb_substr($this->alphabet, $offset) . mb_substr($this->alphabet, 0, $offset);
        $prefix = $alphabet[0];
        $partition = $alphabet[1];
        $alphabet = mb_substr($alphabet, 2);
        $ret = [$prefix];

        for ($i = 0; $i != count($numbers); $i++) {
            $num = $numbers[$i];

            $alphabetWithoutSeparator = mb_substr($alphabet, 0, -1);
            $ret[] = $this->toId($num, $alphabetWithoutSeparator);

            if ($i < count($numbers) - 1) {
                $separator = $alphabet[-1];

                if ($partitioned && $i == 0) {
                    $ret[] = $partition;
                } else {
                    $ret[] = $separator;
                }

                $alphabet = $this->shuffle($alphabet);
            }
        }

        $id = implode('', $ret);

        if ($this->minLength > mb_strlen($id)) {
            if (!$partitioned) {
                array_unshift($numbers, 0);
                $id = $this->encodeNumbers($numbers, true);
            }

            if ($this->minLength > mb_strlen($id)) {
                $id = $id[0] . mb_substr($alphabet, 0, $this->minLength - mb_strlen($id)) . mb_substr($id, 1);
            }
        }

        if ($this->isBlockedId($id)) {
            if ($partitioned) {
                if ($numbers[0] + 1 > self::maxValue()) {
                    throw new \RuntimeException('Ran out of range checking against the blocklist');
                } else {
                    $numbers[0] += 1;
                }
            } else {
                array_unshift($numbers, 0);
            }

            $id = $this->encodeNumbers($numbers, true);
        }

        return $id;
    }

    /**
     * Decodes an ID back into an array of unsigned integers
     *
     * These are the cases where the return value might be an empty array:
     * - Empty ID / empty string
     * - Invalid ID passed (reserved character is in the wrong place)
     * - Non-alphabet character is found within the ID
     *
     * @param string $id Encoded ID
     * @return array<int> Array of unsigned integers
     */
    public function decode(string $id): array
    {
        $ret = [];

        if ($id == '') {
            return $ret;
        }

        $alphabetChars = str_split($this->alphabet);
        foreach (str_split($id) as $c) {
            if (!in_array($c, $alphabetChars)) {
                return $ret;
            }
        }

        $prefix = $id[0];
        $offset = strpos($this->alphabet, $prefix);
        $alphabet = mb_substr($this->alphabet, $offset) . mb_substr($this->alphabet, 0, $offset);
        $partition = $alphabet[1];
        $alphabet = mb_substr($alphabet, 2);
        $id = mb_substr($id, 1);

        $partitionIndex = strpos($id, $partition);
        if ($partitionIndex > 0 && $partitionIndex < mb_strlen($id) - 1) {
            $id = mb_substr($id, $partitionIndex + 1);
            $alphabet = $this->shuffle($alphabet);
        }

        while (mb_strlen($id) > 0) {
            $separator = $alphabet[-1];

            $chunks = explode($separator, $id, 2);
            if (!empty($chunks)) {
                $alphabetWithoutSeparator = mb_substr($alphabet, 0, -1);
                $ret[] = $this->toNumber($chunks[0], $alphabetWithoutSeparator);

                if (count($chunks) > 1) {
                    $alphabet = $this->shuffle($alphabet);
                }
            }

            $id = $chunks[1] ?? '';
        }

        return $ret;
    }

    public static function minValue(): int
    {
        return 0;
    }

    public static function maxValue(): int
    {
        return PHP_INT_MAX;
    }

    protected function shuffle(string $alphabet): string
    {
        $chars = str_split($alphabet);

        for ($i = 0, $j = count($chars) - 1; $j > 0; $i++, $j--) {
            $r = ($i * $j + mb_ord($chars[$i]) + mb_ord($chars[$j])) % count($chars);
            [$chars[$i], $chars[$r]] = [$chars[$r], $chars[$i]];
        }

        return implode('', $chars);
    }

    protected function toId(int $num, string $alphabet): string
    {
        $id = [];
        $chars = str_split($alphabet);

        $result = $num;

        do {
            array_unshift($id, $chars[$this->math->intval($this->math->mod($result, count($chars)))]);
            $result = $this->math->divide($result, count($chars));
        } while ($this->math->greaterThan($result, 0));

        return implode('', $id);
    }

    protected function toNumber(string $id, string $alphabet): int
    {
        $chars = str_split($alphabet);
        return $this->math->intval(array_reduce(str_split($id), function ($a, $v) use ($chars) {
            $number = $this->math->multiply($a, count($chars));
            $number = $this->math->add($number, array_search($v, $chars));

            return $number;
        }, 0));
    }

    protected function isBlockedId(string $id): bool
    {
        $id = strtolower($id);

        foreach ($this->blocklist as $word) {
            if (mb_strlen((string) $word) <= mb_strlen($id)) {
                if (mb_strlen($id) <= 3 || mb_strlen((string) $word) <= 3) {
                    if ($id == $word) {
                        return true;
                    }
                } elseif (preg_match('/~[0-9]+~/', (string) $word)) {
                    if (str_starts_with($id, (string) $word) || strrpos($id, (string) $word) === mb_strlen($id) - mb_strlen((string) $word)) {
                        return true;
                    }
                } elseif (str_contains($id, (string) $word)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get BC Math or GMP extension.
     * @throws \RuntimeException
     */
    protected function getMathExtension(): MathInterface
    {
        if (extension_loaded('gmp')) {
            return new Gmp();
        }

        if (extension_loaded('bcmath')) {
            return new BCMath();
        }

        throw new RuntimeException('Missing math extension for Sqids, install either bcmath or gmp.');
    }
}
