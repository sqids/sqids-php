<?php

namespace Sqids;

use Exception;

class Sqids
{
    public function __construct(private string $alphabet) {
        if (strlen($alphabet) < 5) {
            throw new \InvalidArgumentException('Alphabet must contain at least 5 unique characters.');
        }

        if (strlen($alphabet) !== count(count_chars($alphabet, 1))) {
            throw new \InvalidArgumentException('Alphabet must contain only unique characters.');
        }
    }

    public function encode(array $numbers)
    {
        if (count($numbers) == 0) {
            return '';
        }
    }
}
