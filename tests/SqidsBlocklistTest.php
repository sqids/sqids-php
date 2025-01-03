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
    public function testIfNoCustomBlocklistParamUseDefaultBlocklist()
    {
        $sqids = new Sqids();

        $this->assertSame([4572721], $sqids->decode('aho1e'));
        $this->assertSame('JExTR', $sqids->encode([4572721]));
    }

    public function testIfEmptyBlocklistParamPassedDontUseAnyBlocklist()
    {
        $sqids = new Sqids('', 0, []);

        $this->assertSame([4572721], $sqids->decode('aho1e'));
        $this->assertSame('aho1e', $sqids->encode([4572721]));
    }

    public function testIfNonEmptyBlocklistParamPassedUseOnlyThat()
    {
        $sqids = new Sqids('', 0, [
            'ArUO', // originally encoded [100000]
        ]);

        // Make sure we don't use the default blocklist
        $this->assertSame([4572721], $sqids->decode('aho1e'));
        $this->assertSame('aho1e', $sqids->encode([4572721]));

        // Make sure we are using the passed blocklist
        $this->assertSame([100000], $sqids->decode('ArUO'));
        $this->assertSame('QyG4', $sqids->encode([100000]));
        $this->assertSame([100000], $sqids->decode('QyG4'));
    }

    public function testBlocklist()
    {
        $sqids = new Sqids('', 0, [
            'JSwXFaosAN', // normal result of 1st encoding, let's block that word on purpose
            'OCjV9JK64o', // result of 2nd encoding
            'rBHf', // result of 3rd encoding is `4rBHfOiqd3`, let's block a substring
            '79SM', // result of 4th encoding is `dyhgw479SM`, let's block the postfix
            '7tE6', // result of 4th encoding is `7tE6jdAHLe`, let's block the prefix
        ]);

        $this->assertSame('1aYeB7bRUt', $sqids->encode([1_000_000, 2_000_000]));
        $this->assertSame([1_000_000, 2_000_000], $sqids->decode('1aYeB7bRUt'));
    }

    public function testDecodingBlocklistWordsShouldStillWork()
    {
        $sqids = new Sqids('', 0, ['86Rf07', 'se8ojk', 'ARsz1p', 'Q8AI49', '5sQRZO']);

        $this->assertSame([1, 2, 3], $sqids->decode('86Rf07'));
        $this->assertSame([1, 2, 3], $sqids->decode('se8ojk'));
        $this->assertSame([1, 2, 3], $sqids->decode('ARsz1p'));
        $this->assertSame([1, 2, 3], $sqids->decode('Q8AI49'));
        $this->assertSame([1, 2, 3], $sqids->decode('5sQRZO'));
    }

    public function testMatchAgainstAShortBlocklistWord()
    {
        $sqids = new Sqids('', 0, ['pnd']);
        $this->assertSame([1000], $sqids->decode($sqids->encode([1000])));
    }

    public function testBlocklistFilteringInConstructor()
    {
        // lowercase blocklist in only-uppercase alphabet
        $sqids = new Sqids('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 0, ['sxnzkl']);

        $id = $sqids->encode([1, 2, 3]);
        $numbers = $sqids->decode($id);

        $this->assertSame($id, 'IBSHOZ'); // without blocklist, would've been "SXNZKL"
        $this->assertSame($numbers, [1, 2, 3]);
    }

    public function testMaxEncodingAttempts()
    {
        $alphabet = 'abc';
        $minLength = 3;
        $blocklist = ['cab', 'abc', 'bca'];

        $sqids = new Sqids($alphabet, $minLength, $blocklist);

        $this->assertSame($minLength, strlen($alphabet));
        $this->assertSame(count($blocklist), strlen($alphabet));

        $this->expectException(InvalidArgumentException::class);
        $sqids->encode([0]);
    }

    public function testSpecificIsBlockedIdScenarios()
    {
        $sqids = new Sqids('', 0, ['hey']);
        $this->assertSame('86u', $sqids->encode([100]));

        $sqids = new Sqids('', 0, ['86u']);
        $this->assertSame('sec', $sqids->encode([100]));

        $sqids = new Sqids('', 0, ['vFo']);
        $this->assertSame('gMvFo', $sqids->encode([1000000]));

        $sqids = new Sqids('', 0, ['lP3i']);
        $this->assertSame('oDqljxrokxRt', $sqids->encode([100, 202, 303, 404]));

        $sqids = new Sqids('', 0, ['1HkYs']);
        $this->assertSame('oDqljxrokxRt', $sqids->encode([100, 202, 303, 404]));

        $sqids = new Sqids('', 0, ['0hfxX']);
        $this->assertSame('862REt0hfxXVdsLG8vGWD', $sqids->encode([101, 202, 303, 404, 505, 606, 707]));

        $sqids = new Sqids('', 0, ['hfxX']);
        $this->assertSame('seu8n1jO9C4KQQDxdOxsK', $sqids->encode([101, 202, 303, 404, 505, 606, 707]));
    }
}
