<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

final readonly class ArrayInitialisationPreconditionBuilder implements Builder
{
    public function __construct(
        private PropertyPath $propertyPath,
        private Node\Expr $pathNode
    ) {
    }

    public function getNode(): Node
    {
        return new Node\Stmt\If_(
            new Node\Expr\BooleanNot(
                new Node\Expr\Isset_([$this->pathNode])
            ),
            [
                'stmts' => [
                    new Node\Stmt\Expression(
                        new Node\Expr\Assign(
                            $this->pathNode,
                            new Node\Expr\Array_([], [
                                'kind' => Node\Expr\Array_::KIND_SHORT,
                            ])
                        )
                    ),
                ],
            ]
        );
    }
}
