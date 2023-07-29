<?php

namespace spec\Sqids;

use PhpSpec\ObjectBehavior;
use Sqids\Sqids;

class SqidsSpec extends ObjectBehavior
{
    function it_throw_exception_when_alphabet_is_too_short()
    {
        $this->shouldThrow(new \InvalidArgumentException('Alphabet must contain at least 5 unique characters.'))
            ->during('__construct', ['abcd']);
    }

    function it_trow_exception_when_alphabet_has_not_only_unique_characters()
    {
        $this->shouldThrow(new \InvalidArgumentException('Alphabet must contain only unique characters.'))
            ->during('__construct', ['aabcdefg']);
    }

    function it_encodes_empty_array()
    {
        $this->beConstructedWith('abcde');
        $this->encode([])->shouldReturn('');
    }
}
