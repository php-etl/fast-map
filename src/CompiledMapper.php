<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap;

use Kiboko\Component\FastMap\Compiler\Compiler;
use Kiboko\Contract\Mapping\Compiler\CompilationContextInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class CompiledMapper implements MapperInterface
{
    /** @var iterable<MapperInterface> */
    private readonly iterable $mappers;
    private MapperInterface $compiledMapper;

    public function __construct(
        private readonly Compiler $compiler,
        private readonly CompilationContextInterface $compilationContext,
        MapperInterface ...$mappers
    ) {
        $this->mappers = $mappers;
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        if (null === $this->compiledMapper) {
            $this->compiledMapper = $this->compiler->compile(
                $this->compilationContext,
                ...$this->mappers
            );
        }

        return ($this->compiledMapper)($input, $output, $outputPath);
    }
}
