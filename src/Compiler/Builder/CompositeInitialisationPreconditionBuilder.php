<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathIteratorInterface;

final class CompositeInitialisationPreconditionBuilder implements Builder
{
    /** @var TypeMetadataInterface */
    private $metadata;
    /** @var PropertyPath */
    private $propertyPath;
    /** @var Node\Expr */
    private $pathNode;

    public function __construct(
        TypeMetadataInterface $metadata,
        PropertyPath $propertyPath,
        Node\Expr $pathNode
    ) {
        $this->metadata = $metadata;
        $this->propertyPath = $propertyPath;
        $this->pathNode = $pathNode;
    }

    public function getNode(): Node
    {
        $conditions = new \SplQueue();
        $initialisations = new \SplQueue();

        $pathNode = $this->pathNode;
        foreach ($iterator = $this->propertyPath->getIterator() as $item) {
            if ($iterator->isIndex()) {
                $pathNode = new Node\Expr\ArrayDimFetch(
                    $pathNode,
                    is_int($item) ? new Node\Scalar\LNumber($item) : new Node\Scalar\String_($item)
                );
                
                continue;
            }
            
            if ($iterator->isProperty()) {
                $pathNode = new Node\Expr\PropertyFetch(
                    $pathNode,
                    is_int($item) ? new Node\Scalar\LNumber($item) : new Node\Name($item)
                );
                
                continue;
            }
            
            throw new \RuntimeException('Path spec should be either an array dimension access or an object property access.');
        }

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

    private function propertyFetch(
        PropertyPathIteratorInterface $iterator,
        Node\Expr $pathNode
    ) {
        while ($iterator->valid() && $iterator->isProperty()) {
            $item = $iterator->current();

            $pathNode = new Node\Expr\PropertyFetch(
                $pathNode,
                is_int($item) ? new Node\Scalar\LNumber($item) : new Node\Name($item)
            );

            $iterator->next();
        }
    }
}