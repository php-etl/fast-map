<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Component\FastMap\Contracts;
use \Kiboko\Component\FastMap\Contracts\CompilableInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use PhpParser\Node;

final class ArrayMapper implements
    Contracts\ArrayMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var Contracts\FieldScopingInterface[] */
    private $fields;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(Contracts\FieldScopingInterface ...$fields)
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

    public function compile(Node\Expr $outputNode): array
    {
        $mappers = array_merge(...$this->compileMappers($outputNode));
        return array_merge(
            [
                new Node\Stmt\Expression(
                    expr: new Node\Expr\Assign(
                        var: $outputNode,
                        expr: new Node\Expr\BinaryOp\Coalesce(
                            left: $outputNode,
                            right: new Node\Expr\Array_(attributes: ['kind' => Node\Expr\Array_::KIND_SHORT])
                        ),
                    ),
                ),
            ],
            $mappers,
            [
                new Node\Stmt\Return_(
                    expr: new Node\Expr\Variable('output')
                )
            ],
        );
    }

    private function compileMappers(Node\Expr $outputNode): iterable
    {
        foreach ($this->fields as $mapper) {
            if (!$mapper instanceof Contracts\CompilableInterface) {
                throw new \RuntimeException(strtr(
                    'Expected a %expected%, but got an object of type %actual%.',
                    [
                        '%expected%' => Contracts\CompilableInterface::class,
                        '%actual%' => get_class($mapper),
                    ]
                ));
            }

            yield $mapper->compile($outputNode);
        }
    }
}
