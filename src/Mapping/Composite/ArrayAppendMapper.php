<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Component\SatelliteToolbox\Builder\IsolatedValueAppendingBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ArrayAppendMapper implements Mapping\ArrayMapperInterface, Mapping\CompilableMapperInterface, Mapping\FieldMapperInterface
{
    /** @var Mapping\FieldScopingInterface[] */
    private readonly array $fields;
    private readonly PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables = [];

    public function __construct(Mapping\FieldScopingInterface ...$fields)
    {
        $this->fields = $fields;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        foreach ($this->fields as $field) {
            if ($outputPath->getLength() >= 1) {
                $this->accessor->setValue(
                    $output,
                    $outputPath,
                    $field($input, $output)
                );
            } else {
                $output = $field($input, $output);
            }
        }

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
            (new IsolatedValueAppendingBuilder(
                new Node\Expr\Variable('input'),
                $outputNode,
                array_merge(
                    array_merge(
                        ...$this->compileMappers(new Node\Expr\Variable('output'))
                    ),
                    [
                        new Node\Stmt\Return_(
                            expr: new Node\Expr\Variable('output')
                        ),
                    ],
                ),
                ...$this->contextVariables,
            ))->getNode(),
        ];
    }

    private function compileMappers(Node\Expr $outputNode): iterable
    {
        foreach ($this->fields as $mapper) {
            if (!$mapper instanceof Mapping\CompilableInterface) {
                throw new \RuntimeException(strtr('Expected a %expected%, but got an object of type %actual%.', ['%expected%' => Mapping\CompilableInterface::class, '%actual%' => $mapper::class]));
            }

            yield $mapper->compile($outputNode);
        }
    }
}
