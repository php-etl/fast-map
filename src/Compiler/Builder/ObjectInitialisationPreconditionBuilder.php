<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

class ObjectInitialisationPreconditionBuilder implements Builder
{
    /** @var PropertyPath */
    private $propertyPath;
    /** @var Node\Expr */
    private $pathNode;
    /** @var ClassTypeMetadata */
    private $metadata;

    public function __construct(PropertyPath $propertyPath, Node\Expr $pathNode, ClassTypeMetadata $metadata)
    {
        $this->propertyPath = $propertyPath;
        $this->pathNode = $pathNode;
        $this->metadata = $metadata;
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
                                new Node\Name($this->metadata->name)
                            )
                        )
                    )
                ],
            ]
        );
    }
}