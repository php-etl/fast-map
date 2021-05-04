<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConditionalMapper implements
    Mapping\ObjectMapperInterface,
    Mapping\CompilableMapperInterface,
    Mapping\FieldMapperInterface
{
    private iterable $children;

    public function __construct(
        ArrayConditionalMapper|ObjectConditionalMapper ...$mappers
    ) {
        $this->children = $mappers;
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        foreach ($this->children as $mapper) {
            $output = $mapper($input, $output, $outputPath);
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return array_merge(...array_map(fn (Mapping\CompilableMapperInterface $mapper)
            => $mapper->compile($outputNode)
        ));
    }
}
