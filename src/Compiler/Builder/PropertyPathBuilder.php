<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPath;

class PropertyPathBuilder implements Builder
{
    /** @var PropertyPath */
    private $propertyPath;
    /** @var Node\Expr */
    private $pathNode;
    /** @var int|null */
    private $limit;

    public function __construct(PropertyPath $propertyPath, Node\Expr $pathNode, ?int $limit = null)
    {
        $this->propertyPath = $propertyPath;
        $this->pathNode = $pathNode;
        $this->limit = $limit;
    }

    public function getNode(): Node
    {
        $pathNode = $this->pathNode;

        $iterator = $this->propertyPath->getIterator();
        if ($this->limit < 0) {
            $iterator = new \LimitIterator($iterator, 0, iterator_count($iterator) + $this->limit);
        } else if ($this->limit !== null) {
            $iterator = new \LimitIterator($iterator, 0, $this->limit);
        }

        foreach ($iterator as $index => $child) {
            if ($this->propertyPath->isIndex($index)) {
                $pathNode = new Node\Expr\ArrayDimFetch(
                    $pathNode,
                    new Node\Scalar\String_($child)
                );
            } else if ($this->propertyPath->isProperty($index)) {
                $pathNode = new Node\Expr\PropertyFetch(
                    $pathNode,
                    new Node\Name($child)
                );
            }
        }

        return $pathNode;
    }
}