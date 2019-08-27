<?php

namespace Kiboko\Component\ETL\FastMap\Contracts;

use PhpParser\Node;

interface CompilableMapperInterface extends MapperInterface
{
    /**
     * @return Node[]
     */
    public function compile(): array;
}
