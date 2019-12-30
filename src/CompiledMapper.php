<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContextInterface;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class CompiledMapper implements MapperInterface
{
    /** @var Compiler */
    private $compiler;
    /** @var CompilationContextInterface */
    private $compilationContext;
    /** @var iterable<MapperInterface> */
    private $mappers;
    /** @var MapperInterface */
    private $compiledMapper;

    public function __construct(
        Compiler $compiler,
        CompilationContextInterface $context,
        MapperInterface... $mappers
    ) {
        $this->compiler = $compiler;
        $this->compilationContext = $context;
        $this->mappers = $mappers;
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        if ($this->compiledMapper === null) {
            $this->compiledMapper = $this->compiler->compile(
                $this->compilationContext,
                ...$this->mappers
            );
        }

        return ($this->compiledMapper)($input, $output, $outputPath);
    }
}
