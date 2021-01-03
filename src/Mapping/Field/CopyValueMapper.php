<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Field;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class CopyValueMapper implements
    Contracts\FieldMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var PropertyPathInterface */
    private $inputPaths;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(PropertyPathInterface $inputPaths)
    {
        $this->inputPaths = $inputPaths;
        $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        try {
            $initialValue = $this->accessor->getValue($input, $this->inputPaths);
        } catch (NoSuchIndexException|NoSuchPropertyException $exception) {
            $initialValue = null;
        }

        if ($outputPath->getLength() >= 1) {
            $this->accessor->setValue($output, $outputPath, $initialValue);
        } else {
            $output = $initialValue;
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        $inputPath = new PropertyPath($this->inputPaths);

        return [
            (new RequiredValuePreconditionBuilder($inputPath, new Node\Expr\Variable('input')))->getNode(),
            new Node\Expr\Assign(
                $outputNode,
                (new PropertyPathBuilder($inputPath, new Node\Expr\Variable('input')))->getNode()
            ),
        ];
    }
}