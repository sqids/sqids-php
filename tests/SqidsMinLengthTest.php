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

class SqidsMinLengthTest extends TestCase
{
    public function testSimple()
    {
        $sqids = new Sqids('', strlen(Sqids::DEFAULT_ALPHABET));

        $numbers = [1, 2, 3];
        $id = '86Rf07xd4zBmiJXQG6otHEbew02c3PWsUOLZxADhCpKj7aVFv9I8RquYrNlSTM';

        $this->assertSame($id, $sqids->encode($numbers));
        $this->assertSame($numbers, $sqids->decode($id));
    }

    public function testIncremental()
    {
        $alphabetLength = strlen(Sqids::DEFAULT_ALPHABET);

        $numbers = [1, 2, 3];
        $ids = [
            6 => '86Rf07',
            7 => '86Rf07x',
            8 => '86Rf07xd',
            9 => '86Rf07xd4',
            10 => '86Rf07xd4z',
            11 => '86Rf07xd4zB',
            12 => '86Rf07xd4zBm',
            13 => '86Rf07xd4zBmi',
        ];
        $ids[$alphabetLength + 0] = '86Rf07xd4zBmiJXQG6otHEbew02c3PWsUOLZxADhCpKj7aVFv9I8RquYrNlSTM';
        $ids[$alphabetLength + 1] = '86Rf07xd4zBmiJXQG6otHEbew02c3PWsUOLZxADhCpKj7aVFv9I8RquYrNlSTMy';
        $ids[$alphabetLength + 2] = '86Rf07xd4zBmiJXQG6otHEbew02c3PWsUOLZxADhCpKj7aVFv9I8RquYrNlSTMyf';
        $ids[$alphabetLength + 3] = '86Rf07xd4zBmiJXQG6otHEbew02c3PWsUOLZxADhCpKj7aVFv9I8RquYrNlSTMyf1';

        foreach ($ids as $minLength => $id) {
            $sqids = new Sqids('', $minLength);

            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($minLength, strlen($sqids->encode($numbers)));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    public function testIncrementalNumbers()
    {
        $sqids = new Sqids('', strlen(Sqids::DEFAULT_ALPHABET));

        $ids = [
            'SvIzsqYMyQwI3GWgJAe17URxX8V924Co0DaTZLtFjHriEn5bPhcSkfmvOslpBu' => [0, 0],
            'n3qafPOLKdfHpuNw3M61r95svbeJGk7aAEgYn4WlSjXURmF8IDqZBy0CT2VxQc' => [0, 1],
            'tryFJbWcFMiYPg8sASm51uIV93GXTnvRzyfLleh06CpodJD42B7OraKtkQNxUZ' => [0, 2],
            'eg6ql0A3XmvPoCzMlB6DraNGcWSIy5VR8iYup2Qk4tjZFKe1hbwfgHdUTsnLqE' => [0, 3],
            'rSCFlp0rB2inEljaRdxKt7FkIbODSf8wYgTsZM1HL9JzN35cyoqueUvVWCm4hX' => [0, 4],
            'sR8xjC8WQkOwo74PnglH1YFdTI0eaf56RGVSitzbjuZ3shNUXBrqLxEJyAmKv2' => [0, 5],
            'uY2MYFqCLpgx5XQcjdtZK286AwWV7IBGEfuS9yTmbJvkzoUPeYRHr4iDs3naN0' => [0, 6],
            '74dID7X28VLQhBlnGmjZrec5wTA1fqpWtK4YkaoEIM9SRNiC3gUJH0OFvsPDdy' => [0, 7],
            '30WXpesPhgKiEI5RHTY7xbB1GnytJvXOl2p0AcUjdF6waZDo9Qk8VLzMuWrqCS' => [0, 8],
            'moxr3HqLAK0GsTND6jowfZz3SUx7cQ8aC54Pl1RbIvFXmEJuBMYVeW9yrdOtin' => [0, 9],
        ];

        foreach ($ids as $id => $numbers) {
            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    public function testMinLengths()
    {
        $sqids = new Sqids();

        foreach ([0, 1, 5, 10, strlen(Sqids::DEFAULT_ALPHABET)] as $minLength) {
            foreach ([
                [0],
                [0, 0, 0, 0, 0],
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [100, 200, 300],
                [1_000, 2_000, 3_000],
                [1_000_000],
                [PHP_INT_MAX],
            ] as $numbers) {
                $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, $minLength);

                $id = $sqids->encode($numbers);
                $this->assertGreaterThanOrEqual($minLength, strlen($id));
                $this->assertSame($numbers, $sqids->decode($id));
            }
        }
    }

    public function testOutOfRangeInvalidMinLengthLower()
    {
        $this->expectException(InvalidArgumentException::class);
        new Sqids('', -1);
    }

    public function testOutOfRangeInvalidMinLengthUpper()
    {
        $this->expectException(InvalidArgumentException::class);
        new Sqids('', 256);
    }
}
