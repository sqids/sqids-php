<?php

/**
 * Copyright (c) Sqids maintainers.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/sqids/sqids-php
 */

namespace Sqids\Tests;

use Sqids\Sqids;
use PHPUnit\Framework\TestCase;

class SqidsUniquesTest extends TestCase
{
    public const UPPER = 100000; // Spec const is 1_000_000, but it's taking too long on Github Actions

    public function testUniquesWithPadding()
    {
        $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, strlen(Sqids::DEFAULT_ALPHABET));

        $set = [];
        for ($i = 0; $i !== self::UPPER; $i++) {
            $numbers = [$i];
            $id = $sqids->encode($numbers);
            $set[$id] = 1;
            $this->assertSame($numbers, $sqids->decode($id));
        }

        $this->assertSame(self::UPPER, count($set));
    }

    public function testUniquesLowRanges()
    {
        $sqids = new Sqids();

        $set = [];
        for ($i = 0; $i !== self::UPPER; $i++) {
            $numbers = [$i];
            $id = $sqids->encode($numbers);
            $set[$id] = 1;
            $this->assertSame($numbers, $sqids->decode($id));
        }

        $this->assertSame(self::UPPER, count($set));
    }

    public function testUniquesHighRanges()
    {
        $sqids = new Sqids();

        $set = [];
        for ($i = 100000000; $i !== 100000000 + self::UPPER; $i++) {
            $numbers = [$i];
            $id = $sqids->encode($numbers);
            $set[$id] = 1;
            $this->assertSame($numbers, $sqids->decode($id));
        }

        $this->assertSame(self::UPPER, count($set));
    }

    public function testUniquesMulti()
    {
        $sqids = new Sqids();

        $set = [];
        for ($i = 0; $i !== self::UPPER; $i++) {
            $numbers = [$i, $i, $i, $i, $i];
            $id = $sqids->encode($numbers);
            $set[$id] = 1;
            $this->assertSame($numbers, $sqids->decode($id));
        }

        $this->assertSame(self::UPPER, count($set));
    }
}
