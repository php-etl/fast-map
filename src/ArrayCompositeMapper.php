<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ArrayCompositeMapper implements
    Contracts\ArrayMapperInterface
{
    /** @var string */
    private $outputField;
    /** @var Contracts\ArrayMapperInterface[] */
    private $mappers;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(string $outputField, Contracts\ArrayMapperInterface ...$mappers)
    {
        $this->outputField = $outputField;
        $this->mappers = $mappers;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        foreach ($this->mappers as $mapper) {
            $this->accessor->setValue(
                $output,
                $this->outputField,
                $mapper($input, $this->accessor->getValue($output, $this->outputField) ?? [])
            );
        }

        return $output;
    }
}