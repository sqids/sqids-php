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

class SqidsTest extends TestCase
{
    public function testAlphabet()
    {
        $numbers = [1, 2, 3];
        $id = '8QRLaD';

        $sqids = new Sqids();

        $generated_id = $sqids->encode($numbers);
        $this->assertSame($id, $generated_id);

        $generated_numbers = $sqids->decode($generated_id);
        $this->assertSame($numbers, $generated_numbers);
    }
}