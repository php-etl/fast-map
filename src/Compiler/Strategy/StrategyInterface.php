<?php

namespace Kiboko\Component\FastMap\Compiler\Strategy;

use Kiboko\Component\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\Metadata\ClassMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface StrategyInterface
{
    public function buildTree(PropertyPathInterface $outputPath, ClassMetadataInterface $class, CompilableMapperInterface ...$mappers): array;
}
