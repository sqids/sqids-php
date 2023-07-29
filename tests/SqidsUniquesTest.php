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

class SqidsUniquesTest extends TestCase
{
    public function testSimple()
    {
        $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, strlen(Sqids::DEFAULT_ALPHABET));

        $numbers = [1, 2, 3];
        $id = '75JILToVsGerOADWmHlY38xvbaNZKQ9wdFS0B6kcMEtnRpgizhjU42qT1cd0dL';

        $this->assertSame($id, $sqids->encode($numbers));
        $this->assertSame($numbers, $sqids->decode($id));
    }

    public function testIncrementalNumbers()
    {
        $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, strlen(Sqids::DEFAULT_ALPHABET));

        $ids = [
            'jf26PLNeO5WbJDUV7FmMtlGXps3CoqkHnZ8cYd19yIiTAQuvKSExzhrRghBlwf' => [0, 0],
            'vQLUq7zWXC6k9cNOtgJ2ZK8rbxuipBFAS10yTdYeRa3ojHwGnmMV4PDhESI2jL' => [0, 1],
            'YhcpVK3COXbifmnZoLuxWgBQwtjsSaDGAdr0ReTHM16yI9vU8JNzlFq5Eu2oPp' => [0, 2],
            'OTkn9daFgDZX6LbmfxI83RSKetJu0APihlsrYoz5pvQw7GyWHEUcN2jBqd4kJ9' => [0, 3],
            'h2cV5eLNYj1x4ToZpfM90UlgHBOKikQFvnW36AC8zrmuJ7XdRytIGPawqYEbBe' => [0, 4],
            '7Mf0HeUNkpsZOTvmcj836P9EWKaACBubInFJtwXR2DSzgYGhQV5i4lLxoT1qdU' => [0, 5],
            'APVSD1ZIY4WGBK75xktMfTev8qsCJw6oyH2j3OnLcXRlhziUmpbuNEar05QCsI' => [0, 6],
            'P0LUhnlT76rsWSofOeyRGQZv1cC5qu3dtaJYNEXwk8Vpx92bKiHIz4MgmiDOF7' => [0, 7],
            'xAhypZMXYIGCL4uW0te6lsFHaPc3SiD1TBgw5O7bvodzjqUn89JQRfk2Nvm4JI' => [0, 8],
            '94dRPIZ6irlXWvTbKywFuAhBoECQOVMjDJp53s2xeqaSzHY8nc17tmkLGwfGNl' => [0, 9]
        ];

        foreach ($ids as $id => $numbers) {
            $this->assertSame($id, $sqids->encode($numbers));
            $this->assertSame($numbers, $sqids->decode($id));
        }
    }

    // public function testMinLengths()
    // {
    //     $sqids = new Sqids();

    //     foreach ([0, 1, 5, 10, strlen(Sqids::DEFAULT_ALPHABET)] as $minLength) {
    //         foreach ([
    //             [$sqids->minValue()],
    //             [0, 0, 0, 0, 0],
    //             [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    //             [100, 200, 300],
    //             [1_000, 2_000, 3_000],
    //             [1_000_000],
    //             [$sqids->maxValue()]
    //         ] as $numbers) {
    //             $sqids = new Sqids(Sqids::DEFAULT_ALPHABET, $minLength);

    //             $id = $sqids->encode($numbers);
    //             $this->assertGreaterThanOrEqual($minLength, strlen($id));
    //             $this->assertSame($numbers, $sqids->decode($id));
    //         }
    //     }
    // }

    // public function testOutOfRangeInvalidMinLength()
    // {
    //     $this->expectException(Exception::class);
    //     new Sqids(Sqids::DEFAULT_ALPHABET, -1);

    //     $this->expectException(Exception::class);
    //     $alphabetLength = strlen(Sqids::DEFAULT_OPTIONS['alphabet']);
    //     new Sqids(Sqids::DEFAULT_ALPHABET, strlen(Sqids::DEFAULT_ALPHABET) + 1);
    // }
}