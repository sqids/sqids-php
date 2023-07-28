<?php

namespace Sqids;

class Sqids
{
    public function __construct(private string $alphabet)
    {
        if (strlen($alphabet) < 5) {
            throw new \InvalidArgumentException('Alphabet must contain at least 5 unique characters.');
        }
    }
}
