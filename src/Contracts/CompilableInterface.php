<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Contracts;

use PhpParser\Node;

interface CompilableInterface
{
    /**
     * @return Node[]
     */
    public function compile(Node\Expr $outputNode): array;
}
