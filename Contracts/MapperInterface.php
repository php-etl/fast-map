<?php

namespace Kiboko\Component\ETL\FastMap\Contracts;

interface MapperInterface
{
    public function __invoke($input, $output);
}
