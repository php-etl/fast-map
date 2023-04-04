<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\FastMap\MappingIteration\MappingIterator;
use Kiboko\Contract\Metadata\CompositeTypeMetadataInterface;
use PhpSpec\ObjectBehavior;

final class MappingIteratorSpec extends ObjectBehavior
{
    public function it_is_initializable(CompositeTypeMetadataInterface $metadata): void
    {
        $this->beConstructedWith($metadata);
        $this->shouldHaveType(MappingIterator::class);
    }

    public function it_is_iterable(
        CompositeTypeMetadataInterface $metadata
    ): void {
        $objects = [];

        $objects[] = new class() {
            public $lorem = 'ipsum';
        };
        $objects[] = new class() {
            public $dolor = 'sit';
        };
        $objects[] = new class() {
            public $amet = 'consecutir';
        };
        $objects[] = new class() {
            public $sid = 'nolem';
        };

        $this->beConstructedWith($metadata);
        $this->shouldIterateAs(new \ArrayIterator($objects));
    }
}
