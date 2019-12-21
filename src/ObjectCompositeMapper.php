<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

final class ObjectCompositeMapper implements
    Contracts\ObjectMapperInterface
{
    /** @var Contracts\ObjectInitializerInterface */
    private $initializer;
    /** @var Contracts\ObjectMapperInterface[] */
    private $mappers;

    public function __construct(
        Contracts\ObjectInitializerInterface $initializer,
        Contracts\ObjectMapperInterface ...$mappers
    ) {
        $this->initializer = $initializer;
        $this->mappers = $mappers;
    }

    public function __invoke($input, $output)
    {
        $output = ($this->initializer)($input, $output);
        foreach ($this->mappers as $mapper) {
            $output = $mapper($input, $output);
        }

        return $output;
    }
}