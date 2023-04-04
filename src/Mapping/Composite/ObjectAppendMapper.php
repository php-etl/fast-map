<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Component\SatelliteToolbox\Builder\IsolatedValueAppendingBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ObjectAppendMapper implements Mapping\ObjectMapperInterface, Mapping\CompilableMapperInterface, Mapping\FieldMapperInterface
{
    /** @var Mapping\FieldScopingInterface[] */
    private readonly array $properties;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables = [];

    public function __construct(
        private readonly Mapping\ObjectInitializerInterface $initializer,
        Mapping\FieldScopingInterface ...$properties
    ) {
        $this->properties = $properties;
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $output = ($this->initializer)($input, $output, $outputPath);
        foreach ($this->properties as $property) {
            $output = $property($input, $output);
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
        if (!$this->initializer instanceof Mapping\CompilableObjectInitializerInterface) {
            throw new \RuntimeException(strtr('Expected a %expected%, but got an object of type %actual%.', ['%expected%' => Mapping\CompilableObjectInitializerInterface::class, '%actual%' => $this->initializer::class]));
        }

        return [
            (new IsolatedValueAppendingBuilder(
                new Node\Expr\Variable('input'),
                new Node\Expr\Variable('output'),
                array_merge(
                    $this->initializer->compile($outputNode),
                    array_merge(
                        ...$this->compileMappers($outputNode),
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
        foreach ($this->properties as $mapper) {
            if (!$mapper instanceof Mapping\CompilableInterface) {
                throw new \RuntimeException(strtr('Expected a %expected%, but got an object of type %actual%.', ['%expected%' => Mapping\CompilableInterface::class, '%actual%' => $mapper::class]));
            }

            yield $mapper->compile($outputNode);
        }
    }
}
