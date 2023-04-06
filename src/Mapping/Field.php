<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final readonly class Field implements Mapping\FieldScopingInterface, Mapping\CompilableInterface
{
    private PropertyAccessor $accessor;

    public function __construct(
        private PropertyPathInterface $path,
        private Mapping\CompilableMapperInterface&Mapping\FieldMapperInterface $child
    ) {
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
