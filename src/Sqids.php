<?php

namespace Sqids;

use Exception;

class Sqids
{
    public function __construct(
        private string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    ) {
        if (strlen($alphabet) < 5) {
            throw new \InvalidArgumentException('Alphabet must contain at least 5 unique characters.');
        }

        if (strlen($alphabet) !== count(count_chars($alphabet, 1))) {
            throw new \InvalidArgumentException('Alphabet must contain only unique characters.');
        }

        $this->alphabet = $this->shuffleAlphabet($alphabet);
    }

    public function encode(array $numbers)
    {
        if (count($numbers) == 0) {
            return '';
        }

        return $this->encodeNumbers($numbers);
    }

    private function encodeNumbers(array $numbers, $partitioned = false): string
    {
        $index = 0;
        $offset = array_reduce(
                $numbers,
                function ($carry, $item) use (&$index) {
                    return ord($this->alphabet[$item % strlen($this->alphabet)]) + $index++ + $carry;
                },
                count($numbers),
            ) % strlen($this->alphabet);

        $alphabet = substr($this->alphabet, $offset) . substr($this->alphabet, 0, $offset);

        $prefix = $alphabet[0];
        $partition = $alphabet[1];
        $alphabet = substr($alphabet, 2);

        $ret = [$prefix];

        for ($i = 0; $i != count($numbers); $i++) {
            $number = $numbers[$i];

            $alphabetWithoutSeparator = substr($alphabet, 0, -1);
            $ret[] = $this->toIdentifier($number, $alphabetWithoutSeparator);

            if ($i < count($numbers) - 1) {
                $separator = substr($alphabet, -1);

                if ($partitioned && $i == 0) {
                    $ret[] = $partition;
                } else {
                    $ret[] = $separator;
                }

                $alphabet = $this->shuffleAlphabet($alphabet);
            }
        }

        return implode($ret);
    }

    /** consistent shuffle (always produces the same result given the input) */
    private function shuffleAlphabet(string $alphabet): string
    {
        $chars = str_split($alphabet);
        $len = count($chars);

        for ($i = 0, $j = $len - 1; $j > 0; $i++, $j--) {
            $r = ($i * $j + ord($chars[$i]) + ord($chars[$j])) % $len;
            [$chars[$i], $chars[$r]] = [$chars[$r], $chars[$i]];
        }

        return implode('', $chars);
    }

    private function toIdentifier(mixed $num, string $alphabet): string
    {
        $id = [];
        $chars = str_split($alphabet);

        $result = $num;

        do {
            array_unshift($id, $chars[$result % count($chars)]);
            $result = floor($result / count($chars));
        } while ($result > 0);

        return implode('', $id);
    }
}
