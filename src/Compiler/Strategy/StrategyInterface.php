<?php

namespace Kiboko\Component\ETL\FastMap\Compiler\Strategy;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\Metadata\ClassMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface StrategyInterface
{
    public function buildTree(PropertyPathInterface $outputPath, ClassMetadataInterface $class, CompilableMapperInterface ...$mappers): array;
}