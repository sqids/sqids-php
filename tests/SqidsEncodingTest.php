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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SqidsEncodingTest extends TestCase
{
    public function testSimple()
    {
        $sqids = new Sqids();

        $numbers = [1, 2, 3];
        $id = '86Rf07';

        $this->assertSame($id, $sqids->encode($numbers));
        $this->assertSame($numbers, $sqids->decode($id));
    }

    public function testDifferentInputs()
    {
        $sqids = new Sqids();

        $numbers = [0, 0, 0, 1, 2, 3, 100, 1000, 100000, 1000000, PHP_INT_MAX];
        $this->assertSame($numbers, $sqids->decode($sqids->encode($numbers)));
    }

    public function testIncrementalNumbers()
    {
        $sqids = new Sqids();

        $ids = [
            'bM' => [0],
            'Uk' => [1],
            'gb' => [2],
            'Ef' => [3],
            'Vq' => [4],
            'uw' => [5],
            'OI' => [6],
            'AX' => [7],
            'p6' => [8],
            'nJ' => [9],
        ];

        foreach ($ids as $id => $numbers) {
            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    public function testIncrementalNumbersSameIndex0()
    {
        $sqids = new Sqids();

        $ids = [
            'SvIz' => [0, 0],
            'n3qa' => [0, 1],
            'tryF' => [0, 2],
            'eg6q' => [0, 3],
            'rSCF' => [0, 4],
            'sR8x' => [0, 5],
            'uY2M' => [0, 6],
            '74dI' => [0, 7],
            '30WX' => [0, 8],
            'moxr' => [0, 9],
        ];

        foreach ($ids as $id => $numbers) {
            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    public function testIncrementalNumbersSameIndex1()
    {
        $sqids = new Sqids();

        $ids = [
            'SvIz' => [0, 0],
            'nWqP' => [1, 0],
            'tSyw' => [2, 0],
            'eX68' => [3, 0],
            'rxCY' => [4, 0],
            'sV8a' => [5, 0],
            'uf2K' => [6, 0],
            '7Cdk' => [7, 0],
            '3aWP' => [8, 0],
            'm2xn' => [9, 0],
        ];

        foreach ($ids as $id => $numbers) {
            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    public function testMultiInput()
    {
        $sqids = new Sqids();

        $numbers = range(0, 99);
        $output = $sqids->decode($sqids->encode($numbers));
        $this->assertSame($numbers, $output);
    }

    public function testEncodingNoNumbers()
    {
        $sqids = new Sqids();
        $this->assertSame('', $sqids->encode([]));
    }

    public function testDecodingEmptyString()
    {
        $sqids = new Sqids();
        $this->assertSame([], $sqids->decode(''));
    }

    public function testDecodingAnIdWithAnInvalidCharacter()
    {
        $sqids = new Sqids();
        $this->assertSame([], $sqids->decode('*'));
    }

    public function testEncodeOutOfRangeNumbersLower()
    {
        $this->expectException(InvalidArgumentException::class);

        $sqids = new Sqids();
        $sqids->encode([-1]);
    }
}
