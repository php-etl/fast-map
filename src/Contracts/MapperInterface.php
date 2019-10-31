<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Contracts;

interface MapperInterface
{
    public function __invoke($input, $output);
}
