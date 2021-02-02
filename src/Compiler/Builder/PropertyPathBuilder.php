<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class PropertyPathBuilder implements Builder
{
    public function __construct(
        private PropertyPathInterface $propertyPath,
        private Node\Expr $pathNode,
        private ?int $limit
    ) {
    }

    public function getNode(): Node\Expr
    {
        $pathNode = $this->pathNode;

        $iterator = $this->propertyPath->getIterator();
        if ($this->limit < 0) {
            $iterator = new \LimitIterator($iterator, 0, iterator_count($iterator) + $this->limit);
        } elseif ($this->limit !== null) {
            $iterator = new \LimitIterator($iterator, 0, $this->limit);
        }

        foreach ($iterator as $index => $child) {
            if ($this->propertyPath->isIndex($index)) {
                $pathNode = new Node\Expr\ArrayDimFetch(
                    $pathNode,
                    new Node\Scalar\String_($child)
                );
            } elseif ($this->propertyPath->isProperty($index)) {
                $pathNode = new Node\Expr\PropertyFetch(
                    $pathNode,
                    new Node\Name($child)
                );
            }
        }

        return $pathNode;
    }
}
