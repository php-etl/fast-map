<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Component\FastMap\Compiler\Builder\IsolatedCodeBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ObjectMapper implements
    Mapping\ObjectMapperInterface,
    Mapping\CompilableMapperInterface,
    Mapping\FieldMapperInterface
{
    /** @var Mapping\FieldScopingInterface[] */
    private array $properties;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables;

    public function __construct(
        private Mapping\ObjectInitializerInterface $initializer,
        Mapping\FieldScopingInterface ...$properties
    ) {
        $this->properties = $properties;
        $this->contextVariables = [];
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $output = ($this->initializer)($input, $output, $outputPath);
        foreach ($this->properties as $property) {
            $output = $property($input, $output, $outputPath);
        }

        return $output;
    }

    public function addContextVariable(Node\Expr\Variable $variable): ObjectMapper
    {
        $this->contextVariables[] = $variable;

        return $this;
    }

    public function compile(Node\Expr $outputNode): array
    {
        if (!$this->initializer instanceof Mapping\CompilableObjectInitializerInterface) {
            throw new \RuntimeException(strtr(
                'Expected a %expected%, but got an object of type %actual%.',
                [
                    '%expected%' => Mapping\CompilableObjectInitializerInterface::class,
                    '%actual%' => get_class($this->initializer),
                ]
            ));
        }

        return [
            (new IsolatedCodeBuilder(
                $outputNode,
                new Node\Expr\Variable('output'),
                array_merge(
                    $this->initializer->compile($outputNode),
                    ...$this->compileMappers($outputNode)
                ),
                ...$this->contextVariables,
            ))->getNode(),
            new Node\Stmt\Return_($outputNode),
        ];
    }

    private function compileMappers(Node\Expr $outputNode): iterable
    {
        foreach ($this->properties as $mapper) {
            if (!$mapper instanceof Mapping\CompilableInterface) {
                throw new \RuntimeException(strtr(
                    'Expected a %expected%, but got an object of type %actual%.',
                    [
                        '%expected%' => Mapping\CompilableInterface::class,
                        '%actual%' => get_class($mapper),
                    ]
                ));
            }

            yield $mapper->compile($outputNode);
        }
    }
}
