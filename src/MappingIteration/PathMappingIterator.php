<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingIteration;

use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathIteratorInterface;

class PathMappingIterator implements \Iterator
{
    /** @var PropertyPathIteratorInterface */
    private $inner;
    /** @var TypeMetadataInterface */
    private $metadata;

    public function __construct(PropertyPathIteratorInterface $inner, TypeMetadataInterface $metadata)
    {
        $this->inner = $inner;
        $this->metadata = $metadata;
    }

    public function current()
    {
        return $this->inner->current();
    }

    public function next()
    {
        $this->inner->next();
    }

    public function key()
    {
        return $this->inner->key();
    }

    public function valid()
    {
        return $this->inner->valid();
    }

    public function rewind()
    {
        $this->inner->rewind();
    }
}