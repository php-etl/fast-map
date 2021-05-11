<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class IsolatedCodeBuilder implements Builder
{
    /** @var Node\Stmt[] */
    private array $stmts;
    /** @var Node\Expr[] */
    private array $usedVariables;

    public function __construct(
        private Node\Expr $input,
        private Node\Expr $output,
        array $stmts,
        Node\Expr ...$usedVariables
    ) {
        $this->stmts = $stmts;
        $this->usedVariables = $usedVariables;
    }

    public function getNode(): Node
    {
        return new Node\Expr\Assign(
            $this->output,
            new Node\Expr\FuncCall(
                new Node\Expr\Closure([
                    'params' => [
                        new Node\Param(
                            var: new Node\Expr\Variable('input'),
                        )
                    ],
                    'stmts' => $this->stmts,
                    'uses' => $this->usedVariables
                ]),
                [
                    $this->input,
                ],
            ),
        );
    }
}
