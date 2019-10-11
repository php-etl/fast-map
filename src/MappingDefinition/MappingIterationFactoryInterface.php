<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

interface MappingIterationFactoryInterface
{
    public function matches(TypeMetadataInterface $subject): bool;
    public function walk(TypeMetadataInterface $subject): \Iterator;
}