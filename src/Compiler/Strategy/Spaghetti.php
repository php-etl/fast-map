<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Strategy;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node;

final class Spaghetti implements StrategyInterface
{
    public function buildTree(string $namespace, string $className, CompilableMapperInterface ...$mappers): array
    {
        $factory = new BuilderFactory();

        $stmts = [];
        foreach ($mappers as $mapper) {
            array_push($stmts, ...$mapper->compile());
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
                        ->addStmts($stmts)
                        ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('output')))
                    )
                )
                ->getNode()
        ];
    }
}