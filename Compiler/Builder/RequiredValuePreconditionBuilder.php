<?php

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

class RequiredValuePreconditionBuilder implements Builder
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
                new Node\Expr\Isset_([
                    (new PropertyPathBuilder($this->propertyPath, $this->pathNode))->getNode()
                ])
            ),
            [
                'stmts' => [
                    new Node\Stmt\Throw_(
                        new Node\Expr\New_(new Node\Name(\RuntimeException::class), [
                            new Node\Scalar\String_(strtr(
                                'Could not evaluate path %path%',
                                [
                                    '%path%' => $this->propertyPath,
                                ]
                            ))
                        ])
                    ),
                ]
            ]
        );
    }
}