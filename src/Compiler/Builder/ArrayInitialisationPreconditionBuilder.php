<?php

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

class ArrayInitialisationPreconditionBuilder implements Builder
{
    /** @var PropertyPath */
    private $propertyPath;
    /** @var Node\Expr */
    private $pathNode;

    public function __construct(PropertyPath $propertyPath, Node\Expr $pathNode)
    {
        $this->propertyPath = $propertyPath;
        $this->pathNode = $pathNode;
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
                                'kind' => Node\Expr\Array_::KIND_SHORT
                            ])
                        )
                    )
                ],
            ]
        );
    }
}