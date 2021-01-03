<?php declare(strict_types=1);

namespace performance\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;

interface PerformanceTesting
{
    public function build(): CompilableMapperInterface;

    public function data(int $size): iterable;
}