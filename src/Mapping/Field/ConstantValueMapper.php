<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

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

    public function compile(Node\Expr $outputNode): array
    {
        return [
            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    $outputNode,
                    new Node\Scalar\String_($this->value)
                ),
            ),
        ];
    }
}
