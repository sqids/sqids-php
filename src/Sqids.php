<?php

namespace Sqids;

class Sqids
{
    public function __construct(private string $alphabet)
    {
        if (strlen($alphabet) < 5) {
            throw new \InvalidArgumentException('Alphabet must contain at least 5 unique characters.');
        }

        if (strlen($alphabet) !== count(count_chars($alphabet, 1))) {
            throw new \InvalidArgumentException('Alphabet must contain only unique characters.');
        }
    }
}
