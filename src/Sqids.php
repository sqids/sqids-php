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

class Sqids implements SqidsInterface {
    protected MathInterface $math;

    protected string $alphabet;
    protected int $minLength;
    protected array $blocklist;

    /** @throws \InvalidArgumentException */
    public function __construct(
        string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
		int $minLength = 0,
		array $blocklist = null,
	) {
		$this->math = $this->getMathExtension();

		if (!isset($blocklist)) {
			$blocklist = json_decode(file_get_contents(__DIR__.'/blocklist.json'), false);
		}

        if (strlen($alphabet) < 5) {
            throw new InvalidArgumentException('Alphabet length must be at least 5');
        }

        if (count(array_unique(str_split($alphabet))) != strlen($alphabet)) {
            throw new InvalidArgumentException('Alphabet must contain unique characters');
        }

        if (
            !is_int($minLength) ||
            $minLength < self::minValue() ||
            $minLength > strlen($alphabet)
        ) {
            throw new InvalidArgumentException(
                "Minimum length has to be between {self::minValue()} and {strlen($alphabet)}"
            );
        }

        $filteredBlocklist = [];
        $alphabetChars = str_split($alphabet);
        foreach ((array) $blocklist as $word) {
            if (strlen($word) >= 3) {
                $wordChars = str_split($word);
                $intersection = array_filter($wordChars, function($c) use ($alphabetChars) {
                    return in_array($c, $alphabetChars);
                });
                if (count($intersection) == count($wordChars)) {
                    $filteredBlocklist[] = strtolower($word);
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
	public function encode(array $numbers): string {
		if (count($numbers) == 0) {
			return '';
		}

		$inRangeNumbers = array_filter($numbers, function ($n) {
			return $n >= self::minValue() && $n <= self::maxValue();
		});
		if (count($inRangeNumbers) != count($numbers)) {
			throw new \InvalidArgumentException(
				"Encoding supports numbers between {self::minValue()} and {self::maxValue()}"
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
	protected function encodeNumbers(array $numbers, bool $partitioned = false): string {
		$offset = count($numbers);
		foreach ($numbers as $i => $v) {
			$offset += ord($this->alphabet[$v % strlen($this->alphabet)]) + $i;
		}
		$offset %= strlen($this->alphabet);

		$alphabet = substr($this->alphabet, $offset) . substr($this->alphabet, 0, $offset);
		$prefix = $alphabet[0];
		$partition = $alphabet[1];
		$alphabet = substr($alphabet, 2);
		$ret = [$prefix];

		for ($i = 0; $i != count($numbers); $i++) {
			$num = $numbers[$i];

			$alphabetWithoutSeparator = substr($alphabet, 0, -1);
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

		if ($this->minLength > strlen($id)) {
			if (!$partitioned) {
				array_unshift($numbers, 0);
				$id = $this->encodeNumbers($numbers, true);
			}

			if ($this->minLength > strlen($id)) {
				$id = $id[0] . substr($alphabet, 0, $this->minLength - strlen($id)) . substr($id, 1);
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
	public function decode(string $id): array {
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
		$alphabet = substr($this->alphabet, $offset) . substr($this->alphabet, 0, $offset);
		$partition = $alphabet[1];
		$alphabet = substr($alphabet, 2);
		$id = substr($id, 1);

		$partitionIndex = strpos($id, $partition);
		if ($partitionIndex > 0 && $partitionIndex < strlen($id) - 1) {
			$id = substr($id, $partitionIndex + 1);
			$alphabet = $this->shuffle($alphabet);
		}

		while (strlen($id) > 0) {
			$separator = $alphabet[-1];

			$chunks = explode($separator, $id, 2);
			if (!empty($chunks)) {
				$alphabetWithoutSeparator = substr($alphabet, 0, -1);
				$ret[] = $this->toNumber($chunks[0], $alphabetWithoutSeparator);

				if (count($chunks) > 1) {
					$alphabet = $this->shuffle($alphabet);
				}
			}

			$id = $chunks[1] ?? '';
		}

		return $ret;
	}

	public static function minValue(): int {
		return 0;
	}

	public static function maxValue(): int {
		return PHP_INT_MAX;
	}

	protected function shuffle(string $alphabet): string {
		$chars = str_split($alphabet);

		for ($i = 0, $j = count($chars) - 1; $j > 0; $i++, $j--) {
			$r = ($i * $j + ord($chars[$i]) + ord($chars[$j])) % count($chars);
			list($chars[$i], $chars[$r]) = [$chars[$r], $chars[$i]];
		}

		return implode('', $chars);
	}

	protected function toId(int $num, string $alphabet): string {
		$id = [];
		$chars = str_split($alphabet);

		$result = $num;

		do {
			array_unshift($id, $chars[$result % count($chars)]);
            $result = $this->math->divide($result, count($chars));
        } while ($this->math->greaterThan($result, 0));

		return implode('', $id);
	}

	protected function toNumber(string $id, string $alphabet): int {
		$chars = str_split($alphabet);
		return array_reduce(str_split($id), function ($a, $v) use ($chars) {
			return $a * count($chars) + array_search($v, $chars);
		}, 0);
	}

	protected function isBlockedId(string $id): bool {
		$id = strtolower($id);

		foreach ($this->blocklist as $word) {
			if (strlen($word) <= strlen($id)) {
				if (strlen($id) <= 3 || strlen($word) <= 3) {
					if ($id == $word) {
						return true;
					}
				} else if (preg_match('/~[0-9]+~/', $word)) {
					if (strpos($id, $word) === 0 || strrpos($id, $word) === strlen($id) - strlen($word)) {
						return true;
					}
				} else if (strpos($id, $word) !== false) {
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
