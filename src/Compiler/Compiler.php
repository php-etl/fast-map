<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler;

use Doctrine\Inflector\Inflector as DoctrineInflector;
use Kiboko\Component\ETL\FastMap\Compiler\Strategy\Spaghetti;
use Kiboko\Component\ETL\FastMap\Compiler\Strategy\StrategyInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpParser\PrettyPrinter;

class Compiler
{
    private DoctrineInflector $inflector;
    private StrategyInterface $strategy;

    public function __construct(?StrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new Spaghetti();
        $this->inflector = new DoctrineInflector();
    }

    private function randomIdentifier(): string
    {
        return hash('sha256', \random_bytes(1024));
    }

    private function randomClassName(string $prefix): string
    {
        return $this->inflector->classify($prefix . $this->randomIdentifier());
    }

    public function compile(
        CompilationContextInterface $context,
        MapperInterface ...$mappers
    ) {
        $namespace = $context->getNamespace() ?? 'Kiboko\\__Mapper__';
        $className = $context->getClassName() ?? $this->randomClassName('Mapper');

        $fqcn = (string) ($class = ($context->getClass() ?? sprintf('%s\\%s', $namespace, $className)));
        if (class_exists($fqcn, true)) {
            return new $fqcn();
        }

        if ($context->getFilePath() !== null && file_exists($context->getFilePath())) {
            include $context->getFilePath();
        }

        $tree = $this->strategy->buildTree(
            $context->getPropertyPath(),
            $class,
            ...$mappers
        );

        $prettyPrinter = new PrettyPrinter\Standard();
        if ($context->getFilePath() !== null && is_writable(dirname($context->getFilePath()))) {
            file_put_contents($context->getFilePath(), $prettyPrinter->prettyPrintFile($tree));
            include $context->getFilePath();
        } else {
            include 'data://text/plain;base64,' . base64_encode($prettyPrinter->prettyPrintFile($tree));
        }

        return new $fqcn();
    }
}
