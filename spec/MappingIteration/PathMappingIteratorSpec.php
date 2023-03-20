<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\FastMap\MappingIteration\PathMappingIterator;
use Kiboko\Contract\Metadata\TypeMetadataInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathIterator;
use Symfony\Component\PropertyAccess\PropertyPathIteratorInterface;

final class PathMappingIteratorSpec extends ObjectBehavior
{
    public function it_is_initializable(PropertyPathIteratorInterface $inner, TypeMetadataInterface $metadata): void
    {
        $this->beConstructedWith($inner, $metadata);
        $this->shouldHaveType(PathMappingIterator::class);
    }

    public function it_is_iterable(TypeMetadataInterface $metadata): void
    {
        $this->beConstructedWith(
            new PropertyPathIterator(new PropertyPath('self[user][name]')),
            $metadata
        );

        $this->shouldIterateAs(new \ArrayIterator([
            'self',
            'user',
            'name',
        ]));
    }
}
