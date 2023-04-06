<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Kiboko\Component\FastMap\Compiler\Strategy\Spaghetti;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Kiboko\Contract\Mapping\Compiler\CompilationContextInterface;
use Kiboko\Contract\Mapping\Compiler\Strategy\StrategyInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PhpParser\PrettyPrinter;

class Compiler
{
    private readonly Inflector $inflector;

    public function __construct(private readonly StrategyInterface $strategy = new Spaghetti())
    {
        $this->inflector = InflectorFactory::createForLanguage(Language::ENGLISH)->build();
    }

    private function randomIdentifier(): string
    {
        return hash('sha256', random_bytes(1024));
    }

    private function randomClassName(string $prefix): string
    {
        return $this->inflector->classify($prefix.$this->randomIdentifier());
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
        if (null !== $context->getFilePath() && is_writable(\dirname($context->getFilePath()))) {
            file_put_contents($context->getFilePath(), $prettyPrinter->prettyPrintFile($tree));
            if (!class_exists($fqcn)) {
                include_once $context->getFilePath();
            }
        } else {
            $fs = vfsStream::setup();

            $filename = hash('sha512', random_bytes(512)).'.php';
            $file = new vfsStreamFile($filename);
            $file->setContent($prettyPrinter->prettyPrintFile($tree));
            $fs->addChild($file);
            include_once vfsStream::url('root/'.$filename);
        }

        return new $fqcn();
    }
}
