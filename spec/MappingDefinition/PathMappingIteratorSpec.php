<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\FastMap\MappingDefinition\PathMappingIterator;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathIterator;
use Symfony\Component\PropertyAccess\PropertyPathIteratorInterface;

final class PathMappingIteratorSpec extends ObjectBehavior
{
    function it_is_initializable(PropertyPathIteratorInterface $inner, TypeMetadataInterface $metadata)
    {
        $this->beConstructedWith($inner, $metadata);
        $this->shouldHaveType(PathMappingIterator::class);
    }

    function it_is_iterable(TypeMetadataInterface $metadata)
    {
        $this->beConstructedWith(
            new PropertyPathIterator(new PropertyPath('self[user][name]')),
            $metadata
        );

        $this->shouldIterateAs(new \ArrayIterator([
            'self',
            'user',
            'name'
        ]));
    }
}
