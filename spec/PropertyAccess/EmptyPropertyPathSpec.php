<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\PropertyAccess;

use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class EmptyPropertyPathSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(\IteratorAggregate::class);
        $this->beAnInstanceOf(PropertyPathInterface::class);
    }

    function it_is_is_returning_an_empty_path()
    {
        $this->__toString()->shouldReturn('');
    }

    function it_is_is_have_zero_length()
    {
        $this->getLength()->shouldReturn(0);
    }

    function it_should_have_no_parent()
    {
        $this->getParent()->shouldReturn(null);
    }

    function it_should_be_iterable_with_empty_iterator()
    {
        $this->getIterator()->shouldReturnAnInstanceOf(\EmptyIterator::class);
    }

    function it_should_have_no_elements()
    {
        $this->getElements()->shouldReturn([]);
    }

    function it_should_throw_when_accessing_to_element()
    {
        $this->shouldThrow(new OutOfBoundsException('The index 1 is not within the property path'))
            ->during('getElement', [1]);
    }

    function it_should_throw_when_checking_element_is_property()
    {
        $this->shouldThrow(new OutOfBoundsException('The index 1 is not within the property path'))
            ->during('isProperty', [1]);
    }

    function it_should_throw_when_checking_element_is_index()
    {
        $this->shouldThrow(new OutOfBoundsException('The index 1 is not within the property path'))
            ->during('isIndex', [1]);
    }
}