<?php

namespace Kiboko\Component\ETL\FastMap\Compiler\Strategy;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface StrategyInterface
{
    public function buildTree(PropertyPathInterface $outputPath, string $class, CompilableMapperInterface ...$mappers): array;
}