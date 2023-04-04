<?php

declare(strict_types=1);

namespace KibokoPhpSpecExtension\Matcher;

use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

trait ASTExecutionAwareTrait
{
    private function executeStatements($ast, $input, $output)
    {
        $functionName = '__'.hash('sha512', random_bytes(64)).'__';

        $node = (new Builder\Function_($functionName))
            ->addParam((new Builder\Param('input'))->getNode())
            ->addParam((new Builder\Param('output'))->getNode())
            ->addStmts($ast)
            ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('output')))
            ->getNode()
        ;

        include 'data://text/plain;base64,'.base64_encode((new Standard())->prettyPrintFile([$node]));

        return $functionName($input, $output);
    }
}
