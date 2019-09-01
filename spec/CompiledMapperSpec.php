<?php

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\CompiledMapper;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompiledMapperSpec extends ObjectBehavior
{
    function it_is_initializable(Compiler $compiler, MapperInterface $mapper)
    {
        $this->beConstructedWith($compiler, 'Lorem\\Ipsum', __DIR__, $mapper);
        $this->shouldHaveType(CompiledMapper::class);
    }
}
