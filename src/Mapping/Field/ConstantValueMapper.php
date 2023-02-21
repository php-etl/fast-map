<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConstantValueMapper implements Mapping\FieldMapperInterface, Mapping\CompilableMapperInterface
{
    private readonly PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables = [];

    public function __construct(private mixed $value)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $this->accessor->setValue(
            $output,
            $outputPath,
            $this->value
        );

        return $output;
    }

    public function addContextVariable(Node\Expr\Variable $variable): self
    {
        $this->contextVariables[] = $variable;

        return $this;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return [
            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    $outputNode,
                    $this->compileValue($this->value)
                ),
            ),
        ];
    }

    public function compileValue(mixed $value): Node\Expr
    {
        if (true === $value) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name(name: 'true'),
            );
        }

        if (false === $value) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name(name: 'false'),
            );
        }

        if (null === $value) {
            return new Node\Expr\ConstFetch(
                name: new Node\Name(name: 'null'),
            );
        }

        if (\is_int($value)) {
            return new Node\Scalar\LNumber(value: $value);
        }

        return new Node\Scalar\String_(value: $value);
    }
}
