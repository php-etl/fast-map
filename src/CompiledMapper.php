<?php

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;

class CompiledMapper implements MapperInterface
{
    /** @var Compiler */
    private $compiler;
    /** @var CompilationContext */
    private $compilationContext;
    /** @var MapperInterface */
    private $compiledMapper;

    public function __construct(
        Compiler $compiler,
        string $fqcn,
        string $cachePath,
        MapperInterface... $mappers
    ) {
        $this->compiler = $compiler;

        $namespace = substr($fqcn, 0, strpos($fqcn, '\\') + 1);
        $className = substr($fqcn, strpos($fqcn, '\\') + 1);

        $this->compilationContext = new CompilationContext(
            $cachePath . $className . '.php',
            $namespace,
            $className,
            ...$mappers
        );
    }

    public function __invoke($input, $output): array
    {
        if ($this->compiledMapper === null) {
            $this->compiledMapper = $this->compiler->compile($this->compilationContext);
        }

        return $this->compiledMapper->map($input, $output);
    }
}
