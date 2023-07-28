<?php

namespace spec\Sqids;

use PhpSpec\ObjectBehavior;
use Sqids\Sqids;

class SqidsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Sqids::class);
    }
}
