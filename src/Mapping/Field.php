<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class Field implements
    Contracts\FieldScopingInterface,
    Contracts\CompilableInterface
{
    /** @var PropertyPathInterface */
    private $outputField;
    /** @var Contracts\MapperInterface */
    private $child;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        PropertyPathInterface $outputField,
        Contracts\FieldMapperInterface $child
    ) {
        $this->outputField = $outputField;
        $this->child = $child;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        return ($this->child)($input, $output, $this->path);
    }

    public function compile(Node\Expr $outputNode): array
    {
        return $this->child->compile((new PropertyPathBuilder($this->path, $outputNode))->getNode());
    }
}