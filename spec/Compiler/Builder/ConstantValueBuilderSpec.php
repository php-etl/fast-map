<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Compiler\Builder;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\ConstantValueBuilder;
use PhpParser\Builder;
use PhpParser\Node;
use PhpSpec\ObjectBehavior;

final class ConstantValueBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(ConstantValueBuilder::class);
        $this->beAnInstanceOf(Builder::class);
    }

    function it_is_building_string()
    {
        $this->beConstructedWith('Lorem ipsum');
        $this->getNode()->shouldBeLike(new Node\Scalar\String_('Lorem ipsum'));
    }

    function it_is_building_integer()
    {
        $this->beConstructedWith(1);
        $this->getNode()->shouldBeLike(new Node\Scalar\LNumber(1));
    }

    function it_is_building_double()
    {
        $this->beConstructedWith(1.0);
        $this->getNode()->shouldBeLike(new Node\Scalar\DNumber(1.0));
    }

    function it_is_building_empty_array()
    {
        $this->beConstructedWith([]);
        $this->getNode()->shouldBeLike(new Node\Expr\Array_());
    }

    function it_is_building_indexed_array()
    {
        $this->beConstructedWith([
            'Lorem ipsum',
            1,
            1.1
        ]);
        $this->getNode()->shouldBeLike(new Node\Expr\Array_([
            new Node\Expr\ArrayItem(new Node\Scalar\String_('Lorem ipsum'), new Node\Scalar\LNumber(0)),
            new Node\Expr\ArrayItem(new Node\Scalar\LNumber(1), new Node\Scalar\LNumber(1)),
            new Node\Expr\ArrayItem(new Node\Scalar\DNumber(1.1), new Node\Scalar\LNumber(2)),
        ]));
    }

    function it_is_building_keyed_array()
    {
        $this->beConstructedWith([
            'item1' => 'Lorem ipsum',
            'item2' => 1,
            'item3' => 1.1
        ]);
        $this->getNode()->shouldBeLike(new Node\Expr\Array_([
            new Node\Expr\ArrayItem(new Node\Scalar\String_('Lorem ipsum'), new Node\Scalar\String_('item1')),
            new Node\Expr\ArrayItem(new Node\Scalar\LNumber(1), new Node\Scalar\String_('item2')),
            new Node\Expr\ArrayItem(new Node\Scalar\DNumber(1.1), new Node\Scalar\String_('item3')),
        ]));
    }

    function it_is_failing_with_objects()
    {
        $this->beConstructedWith(new \stdClass());
        $this->shouldThrow(new \RuntimeException('Could not handle static value of type stdClass, only string, double, integer and array are supported.'))
            ->during('getNode');
    }

    function it_is_failing_with_closure()
    {
        $this->beConstructedWith(function(){});
        $this->shouldThrow(new \RuntimeException('Could not handle static value of type Closure, only string, double, integer and array are supported.'))
            ->during('getNode');
    }

    function it_is_failing_with_resource()
    {
        $this->beConstructedWith(STDOUT);
        $this->shouldThrow(new \RuntimeException('Could not handle static value of type resource, only string, double, integer and array are supported.'))
            ->during('getNode');
    }
}