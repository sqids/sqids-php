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

class SqidsBlocklistTest extends TestCase
{
    // public function testIfNoCustomBlocklistParamUseDefaultBlocklist()
    // {
    //     $sqids = new Sqids();

    //     $this->assertSame([200044], $sqids->decode('sexy'));
    //     $this->assertSame('d171vI', $sqids->encode([200044]));
    // }

    // public function testIfEmptyBlocklistParamPassedDontUseAnyBlocklist()
    // {
    //     $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, Sqids::DEFAULT_MIN_LENGTH, []);

    //     $this->assertSame([200044], $sqids->decode('sexy'));
    //     $this->assertSame('sexy', $sqids->encode([200044]));
    // }

    // public function testIfNonEmptyBlocklistParamPassedUseOnlyThat()
    // {
    //     $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, Sqids::DEFAULT_MIN_LENGTH, [
    //         'AvTg' // originally encoded [100000]
    //     ]);

    //     // Make sure we don't use the default blocklist
    //     $this->assertSame([200044], $sqids->decode('sexy'));
    //     $this->assertSame('sexy', $sqids->encode([200044]));

    //     // Make sure we are using the passed blocklist
    //     $this->assertSame([100000], $sqids->decode('AvTg'));
    //     $this->assertSame('7T1X8k', $sqids->encode([100000]));
    //     $this->assertSame([100000], $sqids->decode('7T1X8k'));
    // }

    public function testBlocklist()
    {
        $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, Sqids::DEFAULT_MIN_LENGTH, [
            '8QRLaD', // normal result of 1st encoding, let's block that word on purpose
            '7T1cd0dL', // result of 2nd encoding
            'UeIe', // result of 3rd encoding is `RA8UeIe7`, let's block a substring
            'imhw', // result of 4th encoding is `WM3Limhw`, let's block the postfix
            'LfUQ' // result of 4th encoding is `LfUQh4HN`, let's block the prefix
        ]);

        $this->assertSame('TM0x1Mxz', $sqids->encode([1, 2, 3]));
        $this->assertSame([1, 2, 3], $sqids->decode('TM0x1Mxz'));
    }

    public function testDecodingBlocklistWordsShouldStillWork()
    {
        $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, Sqids::DEFAULT_MIN_LENGTH, ['8QRLaD', '7T1cd0dL', 'RA8UeIe7', 'WM3Limhw', 'LfUQh4HN']);

        $this->assertSame([1, 2, 3], $sqids->decode('8QRLaD'));
        $this->assertSame([1, 2, 3], $sqids->decode('7T1cd0dL'));
        $this->assertSame([1, 2, 3], $sqids->decode('RA8UeIe7'));
        $this->assertSame([1, 2, 3], $sqids->decode('WM3Limhw'));
        $this->assertSame([1, 2, 3], $sqids->decode('LfUQh4HN'));
    }

    // public function testMatchAgainstAShortBlocklistWord()
    // {
    //     $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, Sqids::DEFAULT_MIN_LENGTH, ['pPQ']);
    //     $this->assertSame([1000], $sqids->decode($sqids->encode([1000])));
    // }
}
