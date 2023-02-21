<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use Kiboko\Component\Metadata\ClassTypeMetadata;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

final readonly class ObjectInitialisationPreconditionBuilder implements Builder
{
    public function __construct(
        private PropertyPath $propertyPath,
        private Node\Expr $pathNode,
        private ClassTypeMetadata $metadata
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
                            new Node\Expr\New_(
                                new Node\Name($this->metadata->getName())
                            )
                        )
                    ),
                ],
            ]
        );
    }
}
