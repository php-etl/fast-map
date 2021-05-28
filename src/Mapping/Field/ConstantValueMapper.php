<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Mapping\Composite\ArrayMapper;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConstantValueMapper implements
    Mapping\FieldMapperInterface,
    Mapping\CompilableMapperInterface
{
    private PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables;

    public function __construct(private mixed $value)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->contextVariables = [];
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

    public function addContextVariable(Node\Expr\Variable $variable): ConstantValueMapper
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
                    new Node\Scalar\String_($this->value)
                ),
            ),
            new Node\Stmt\Return_(new Node\Expr\Variable('output'))
        ];
    }
}
