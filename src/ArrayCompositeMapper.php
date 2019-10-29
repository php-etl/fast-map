<?php

namespace Kiboko\Component\ETL\FastMap;

class ArrayCompositeMapper implements
    Contracts\ArrayMapperInterface
{
    /** @var Contracts\ArrayMapperInterface[] */
    private $mappers;

    public function __construct(Contracts\ArrayMapperInterface ...$mappers)
    {
        $this->mappers = $mappers;
    }

    public function __invoke($input, $output)
    {
        foreach ($this->mappers as $mapper) {
            $output = $mapper($input, $output);
        }

        return $output;
    }
}