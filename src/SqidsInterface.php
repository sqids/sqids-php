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

interface SqidsInterface
{
    /**
     * Encode integers to generate an ID.
     * @param array<int> $numbers
     * @return string
     */
    public function encode(array $numbers): string;

    /**
     * Decode an ID back to integers.
     * @param string $id
     * @return array<int>
     */
    public function decode(string $id): array;
}
