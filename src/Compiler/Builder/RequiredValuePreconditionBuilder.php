<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

final readonly class RequiredValuePreconditionBuilder implements Builder
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
                new Node\Expr\Isset_([
                    (new PropertyPathBuilder($this->propertyPath, $this->pathNode))->getNode(),
                ])
            ),
            [
                'stmts' => [
                    new Node\Stmt\Throw_(
                        new Node\Expr\New_(new Node\Name\FullyQualified(\RuntimeException::class), [
                            new Node\Arg(
                                new Node\Scalar\String_(strtr(
                                    'Could not evaluate path %path%',
                                    [
                                        '%path%' => $this->propertyPath,
                                    ],
                                )),
                            ),
                        ]),
                    ),
                ],
            ]
        );
    }
}
