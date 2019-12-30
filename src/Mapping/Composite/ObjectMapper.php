<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Composite;

use Kiboko\Component\ETL\FastMap\Contracts;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ObjectMapper implements
    Contracts\ObjectMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var Contracts\ObjectInitializerInterface */
    private $initializer;
    /** @var Contracts\FieldScopingInterface[] */
    private $properties;

    public function __construct(
        Contracts\ObjectInitializerInterface $initializer,
        Contracts\FieldScopingInterface ...$properties
    ) {
        $this->initializer = $initializer;
        $this->properties = $properties;
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $output = ($this->initializer)($input, $output, $outputPath);
        foreach ($this->properties as $property) {
            $output = $property($input, $output, $outputPath);
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        if (!$this->initializer instanceof Contracts\CompilableObjectInitializerInterface) {
            throw new \RuntimeException(strtr(
                'Expected a %expected%, but got an object of type %actual%.',
                [
                    '%expected%' => Contracts\CompilableObjectInitializerInterface::class,
                    '%actual%' => get_class($this->initializer),
                ]
            ));
        }

        return array_merge(
            $this->initializer->compile($outputNode),
            ...$this->compileMappers($outputNode)
        );
    }

    private function compileMappers(Node\Expr $outputNode): iterable
    {
        foreach ($this->properties as $mapper) {
            if (!$mapper instanceof Contracts\CompilableMapperInterface) {
                throw new \RuntimeException(strtr(
                    'Expected a %expected%, but got an object of type %actual%.',
                    [
                        '%expected%' => Contracts\CompilableMapperInterface::class,
                        '%actual%' => get_class($mapper),
                    ]
                ));
            }

            yield $mapper->compile($outputNode);
        }
    }
}