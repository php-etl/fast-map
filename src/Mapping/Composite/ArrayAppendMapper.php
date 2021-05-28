<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Component\FastMap\Compiler\Builder\IsolatedCodeAppendVariableBuilder;
use Kiboko\Component\SatelliteToolbox\Builder\IsolatedValueAppendingBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ArrayAppendMapper implements
    Mapping\ArrayMapperInterface,
    Mapping\CompilableMapperInterface,
    Mapping\FieldMapperInterface
{
    /** @var Mapping\FieldScopingInterface[] */
    private array $fields;
    private PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables;

    public function __construct(Mapping\FieldScopingInterface ...$fields)
    {
        $this->fields = $fields;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->contextVariables = [];
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

    public function addContextVariable(Node\Expr\Variable $variable): ArrayAppendMapper
    {
        $this->contextVariables[] = $variable;

        return $this;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return [
            new Node\Stmt\Expression(
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
                            )
                        ],
                    ),
                    ...$this->contextVariables,
                ))->getNode()
            ),
            new Node\Stmt\Return_(new Node\Expr\Variable('output'))
        ];
    }

    private function compileMappers(Node\Expr $outputNode): iterable
    {
        foreach ($this->fields as $mapper) {
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
