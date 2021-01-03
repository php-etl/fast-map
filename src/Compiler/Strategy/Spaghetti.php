<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Strategy;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class Spaghetti implements StrategyInterface
{
    public function buildTree(PropertyPathInterface $outputPath, string $class, CompilableMapperInterface ...$mappers): array
    {
        $factory = new BuilderFactory();

        $stmts = [];
        foreach ($mappers as $mapper) {
            array_push($stmts, ...$mapper->compile(
                (new PropertyPathBuilder($outputPath, new Node\Expr\Variable('output')))->getNode()
            ));
        }

        return [
            $factory->namespace((string) $class->getNamespace())
//                ->addStmt($factory->use(CompiledMapperInterface::class))
                ->addStmt($factory->class((string) $class->getName())
                    ->implement(new Node\Name\FullyQualified(CompiledMapperInterface::class))
                    ->makeFinal()
                    ->addStmt($factory->method('__invoke')
                        ->makePublic()
                        ->addParam($factory->param('input'))
                        ->addParam($factory->param('output')->setDefault(null))
                        ->addStmts($stmts)
                        ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('output')))
                    )
                )
                ->getNode()
        ];
    }
}