<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

class ObjectCompositeMapper implements
    Contracts\ObjectMapperInterface
{
    /** @var Contracts\ObjectMapperInterface[] */
    private $mappers;

    public function __construct(Contracts\ObjectMapperInterface ...$mappers)
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