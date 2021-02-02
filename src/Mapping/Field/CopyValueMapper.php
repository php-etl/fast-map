<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class CopyValueMapper implements
    Mapping\FieldMapperInterface,
    Mapping\CompilableMapperInterface
{
    private PropertyAccessor $accessor;

    public function __construct(private PropertyPathInterface $inputPaths)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $this->accessor->setValue(
            $output,
            $outputPath,
            $this->accessor->getValue($input, $this->inputPaths) ?? null
        );

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        $inputPath = new PropertyPath($this->inputPaths);

        return [
            (new RequiredValuePreconditionBuilder($inputPath, new Node\Expr\Variable('input')))->getNode(),

            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    $outputNode,
                    (new PropertyPathBuilder($inputPath, new Node\Expr\Variable('input')))->getNode()
                ),
            ),
        ];
    }
}
