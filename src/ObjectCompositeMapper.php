<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableObjectInitializerInterface;

final class ObjectCompositeMapper implements
    Contracts\ObjectMapperInterface,
    Contracts\CompilableMapperInterface
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

    public function compile(): array
    {
        if (!$this->initializer instanceof CompilableObjectInitializerInterface) {
            throw new \RuntimeException(strtr(
                'Expected a %expected%, but got an object of type %actual%.',
                [
                    '%expected%' => CompilableObjectInitializerInterface::class,
                    '%actual%' => get_class($this->initializer),
                ]
            ));
        }

        return array_merge(
            $this->initializer->compile(),
            ...$this->compileMappers()
        );
    }

    private function compileMappers(): iterable
    {
        foreach ($this->mappers as $mapper) {
            if (!$mapper instanceof CompilableMapperInterface) {
                throw new \RuntimeException(strtr(
                    'Expected a %expected%, but got an object of type %actual%.',
                    [
                        '%expected%' => CompilableMapperInterface::class,
                        '%actual%' => get_class($mapper),
                    ]
                ));
            }

            yield $mapper->compile();
        }
    }
}