<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Kiboko\Component\ETL\FastMap\Compiler\Strategy\Spaghetti;
use Kiboko\Component\ETL\FastMap\Compiler\Strategy\StrategyInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpParser\PrettyPrinter;

class Compiler
{
    /** @var Inflector */
    private $inflector;
    /** @var StrategyInterface */
    private $strategy;

    public function __construct(?StrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new Spaghetti();
        $this->inflector = (new InflectorFactory())();
    }

    private function randomIdentifier(): string
    {
        return hash('sha256', random_bytes(1024));
    }

    private function randomClassName(string $prefix): string
    {
        return $this->inflector->classify($prefix . $this->randomIdentifier());
    }

    public function compile(
        CompilationContextInterface $context,
        MapperInterface ...$mappers
    ) {
        $namespace = $context->namespace() ?? 'Kiboko\\__Mapper__\\';
        $className = $context->className() ?? $this->randomClassName('Mapper');

        if ($context->path() !== null && file_exists($context->path())) {
            include $context->path();
        }

        $fqcn = $namespace . $className;
        if (class_exists($fqcn, true)) {
            return new $fqcn();
        }

        $tree = $this->strategy->buildTree(
            $namespace,
            $className,
            ...$mappers
        );

        $prettyPrinter = new PrettyPrinter\Standard();
        if ($context->path() !== null && is_writable(dirname($context->path()))) {
            file_put_contents($context->path(), $prettyPrinter->prettyPrintFile($tree));
            include $context->path();
        } else {
            include 'data://text/plain;base64,' . base64_encode($prettyPrinter->prettyPrintFile($tree));
        }

        return new $fqcn();
    }
}
