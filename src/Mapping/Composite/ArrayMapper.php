<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Composite;

use Kiboko\Component\ETL\FastMap\Contracts;
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
        // TODO: Implement compile() method.
    }
}