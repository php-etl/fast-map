<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Contracts;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class CollectionValueMapper implements
    Contracts\ArrayMapperInterface
{
    /** @var string */
    private $outputField;
    /** @var string */
    private $inputField;
    /** @var PropertyAccessor */
    private $accessor;
    /** @var Contracts\MapperInterface */
    private $inner;

    public function __construct(
        string $outputField,
        string $inputField,
        Contracts\MapperInterface $inner
    ) {
        $this->outputField = $outputField;
        $this->inputField = $inputField;
        $this->inner = $inner;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        $input = $this->accessor->getValue($input, $this->inputField);
        if (!is_iterable($input)) {
            throw new \InvalidArgumentException(strtr(
                'The data at path %path% in first argument should be iterable.',
                [
                    '%path%' => $this->inputField,
                ]
            ));
        }

        $collection = $this->accessor->getValue($output, $this->outputField) ?? [];
        $index = 0;
        foreach ($input as $item) {
            $this->accessor->setValue(
                $collection,
                sprintf('[%d]', $index),
                ($this->inner)(
                    $item,
                    $this->accessor->getValue($collection, sprintf('[%d]', $index)) ?? []
                )
            );

            $index++;
        }

        $this->accessor->setValue(
            $output,
            $this->outputField,
            $collection
        );

        return $output;
    }
}