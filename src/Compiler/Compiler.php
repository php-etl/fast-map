<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\PrettyPrinter;

class Compiler
{
    /** @var Inflector */
    private $inflector;

    public function __construct()
    {
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

    private function randomMethodName(string $prefix): string
    {
        return $prefix . $this->randomIdentifier();
    }

    public function compile(CompilationContext $context)
    {
        $namespace = $context->namespace ?? 'Kiboko\\__Mapper__\\';
        $className = $context->className ?? $this->randomClassName('Mapper');

        if (file_exists($context->path)) {
            include $context->path;
        }

        $fqcn = $namespace . $className;
        if (class_exists($fqcn, true)) {
            return new $fqcn();
        }

        $tree = $this->buildTree(
            $namespace,
            $className,
            ...$context->mappers
        );

        $prettyPrinter = new PrettyPrinter\Standard();
        echo $prettyPrinter->prettyPrintFile($tree);
        if ($context->path !== null && is_writable(dirname($context->path))) {
            file_put_contents($context->path, $prettyPrinter->prettyPrintFile($tree));
            include $context->path;
        } else {
            include 'data://text/plain;base64,' . base64_encode($prettyPrinter->prettyPrintFile($tree));
        }

        return new $fqcn();
    }

    public function buildTree(string $namespace, string $className, CompilableMapperInterface ...$mappers): array
    {
        $factory = new BuilderFactory();

        $calls = [
            new Node\Expr\Array_([], [
                'kind' => Node\Expr\Array_::KIND_SHORT
            ])
        ];
        $methods = [];
        foreach ($mappers as $mapper) {
            $methodName = $this->randomMethodName('map_');

            $calls[] = new Node\Expr\MethodCall(
                new Node\Expr\Variable('this'),
                $methodName,
                [
                    new Node\Expr\Variable('input'),
                    new Node\Expr\Variable('output'),
                ]
            );
            $methods[] = $this->wrapMapping($methodName, $factory, $mapper->compile());
        }

        return [
            $factory->namespace(rtrim($namespace, '\\'))
//                ->addStmt($factory->use(MapperInterface::class))
                ->addStmt($factory->class($className)
                    ->implement(new Node\Name\FullyQualified(MapperInterface::class))
                    ->makeFinal()
                    ->addStmt($factory->method('__invoke')
                        ->makePublic()
                        ->addParam($factory->param('input'))
                        ->addParam($factory->param('output'))
                        ->addStmt(new Node\Stmt\Return_(
                            new Node\Expr\FuncCall(
                                new Node\Name('array_merge'),
                                $calls
                            )
                        ))
                    )
                    ->addStmts($methods)
                )
                ->getNode()
        ];
    }

    private function wrapMapping(string $methodName, BuilderFactory $factory, array $statements)
    {
        return $factory->method($methodName)
            ->makeFinal()
            ->makePrivate()
            ->addParam($factory->param('input'))
            ->addParam($factory->param('output'))
            ->addStmts(array_merge(
                $statements,
                [
                    new Node\Stmt\Return_(
                        new Node\Expr\Variable('output')
                    )
                ]
            ));
    }
}
