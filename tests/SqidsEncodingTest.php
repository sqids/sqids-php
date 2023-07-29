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
        $id = '8QRLaD';

        $this->assertSame($id, $sqids->encode($numbers));
        $this->assertSame($numbers, $sqids->decode($id));
    }

    // public function testDifferentInputs()
    // {
    //     $sqids = new Sqids();

    //     $numbers = [0, 0, 0, 1, 2, 3, 100, 1000, 100000, 1000000, $sqids->maxValue()];
    //     $this->assertSame($numbers, $sqids->decode($sqids->encode($numbers)));
    // }

    public function testIncrementalNumbers()
    {
        $sqids = new Sqids();

        $ids = [
            'bV' => [0],
            'U9' => [1],
            'g8' => [2],
            'Ez' => [3],
            'V8' => [4],
            'ul' => [5],
            'O3' => [6],
            'AF' => [7],
            'ph' => [8],
            'n8' => [9]
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
            'SrIu' => [0, 0],
            'nZqE' => [0, 1],
            'tJyf' => [0, 2],
            'e86S' => [0, 3],
            'rtC7' => [0, 4],
            'sQ8R' => [0, 5],
            'uz2n' => [0, 6],
            '7Td9' => [0, 7],
            '3nWE' => [0, 8],
            'mIxM' => [0, 9]
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
            'SrIu' => [0, 0],
            'nbqh' => [1, 0],
            't4yj' => [2, 0],
            'eQ6L' => [3, 0],
            'r4Cc' => [4, 0],
            'sL82' => [5, 0],
            'uo2f' => [6, 0],
            '7Zdq' => [7, 0],
            '36Wf' => [8, 0],
            'm4xT' => [9, 0]
        ];

        foreach ($ids as $id => $numbers) {
            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    // public function testMultiInput()
    // {
    //     $sqids = new Sqids();

    //     $numbers = range(0, 99);
    //     $output = $sqids->decode($sqids->encode($numbers));
    //     $this->assertSame($numbers, $output);
    // }

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

    public function testEncodeOutOfRangeNumbers()
    {
        $this->expectException(InvalidArgumentException::class);

        $sqids = new Sqids();
        $sqids->encode([$sqids->minValue() - 1]);
        $sqids->encode([$sqids->maxValue() + 1]);
    }
}