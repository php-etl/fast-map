<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;
use Kiboko\Component\FastMap\Compiler\Strategy\Spaghetti;
use Kiboko\Component\FastMap\Compiler\Strategy\StrategyInterface;
use Kiboko\Component\FastMap\Contracts\MapperInterface;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use PhpParser\PrettyPrinter;

class Compiler
{
    /** @var DoctrineInflector */
    private $inflector;
    /** @var StrategyInterface */
    private $strategy;

    public function __construct(?StrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new Spaghetti();
        $this->inflector = new DoctrineInflector();
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
        $namespace = $context->getNamespace() ?? 'Kiboko\\__Mapper__';
        $className = $context->getClassName() ?? $this->randomClassName('Mapper');

        $fqcn = (string) ($class = ($context->getClass() ?? new ClassReferenceMetadata($className, $namespace)));

        $tree = $this->strategy->buildTree(
            $context->getPropertyPath(),
            $class,
            ...$mappers
        );

        $prettyPrinter = new PrettyPrinter\Standard();
        if ($context->getFilePath() !== null && is_writable(dirname($context->getFilePath()))) {
            file_put_contents($context->getFilePath(), $prettyPrinter->prettyPrintFile($tree));
            if (!class_exists($fqcn)) {
                include_once $context->getFilePath();
            }
        } else {
            include_once 'data://text/plain;base64,' . base64_encode($prettyPrinter->prettyPrintFile($tree));
        }

        return new $fqcn();
    }
}
