<?php

namespace Kiboko\Component\ETL\FastMap\Compiler\Strategy;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;

interface StrategyInterface
{
    public function buildTree(string $namespace, string $className, CompilableMapperInterface ...$mappers): array;
}