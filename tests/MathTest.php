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

use Sqids\Math\BCMath;
use Sqids\Math\Gmp;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

class MathTest extends TestCase
{
    public static function mathProvider(): array
    {
        $providerCases = [];
        if (extension_loaded('gmp')) {
            $providerCases[] = [new Gmp()];
        }

        if (extension_loaded('bcmath')) {
            $providerCases[] = [new BCMath()];
        }

        if (count($providerCases) > 0) {
            return $providerCases;
        }

        throw new RuntimeException('Missing math extension for Sqids, install either bcmath or gmp.');
    }

    #[DataProvider('mathProvider')]
    public function testAdd($math): void
    {
        $this->assertEquals($math->get(3), $math->add(1, 2));
    }

    #[DataProvider('mathProvider')]
    public function testMultiply($math): void
    {
        $this->assertEquals($math->get(12), $math->multiply(2, 6));
    }

    #[DataProvider('mathProvider')]
    public function testDivide($math): void
    {
        $this->assertEquals($math->get(2), $math->divide(4, 2));
    }

    #[DataProvider('mathProvider')]
    public function testGreaterThan($math): void
    {
        $this->assertTrue($math->greaterThan('18446744073709551615', '9223372036854775807'));
        $this->assertFalse($math->greaterThan('9223372036854775807', '18446744073709551615'));
        $this->assertFalse($math->greaterThan('9223372036854775807', '9223372036854775807'));
    }

    #[DataProvider('mathProvider')]
    public function testMod($math): void
    {
        $this->assertEquals($math->get(15), $math->mod('18446744073709551615', '100'));
    }

    #[DataProvider('mathProvider')]
    public function testIntval($math): void
    {
        $this->assertSame(9223372036854775807, $math->intval('9223372036854775807'));
    }

    #[DataProvider('mathProvider')]
    public function testStrval($math): void
    {
        $this->assertSame('18446744073709551615', $math->strval($math->add('0', '18446744073709551615')));
    }
}
