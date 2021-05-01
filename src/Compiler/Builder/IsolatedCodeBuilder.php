<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class IsolatedCodeBuilder implements Builder
{
    /** @var Node\Stmt[] */
    private array $stmts;

    public function __construct(
        private Node\Expr $input,
        private Node\Expr $output,
        array $stmts
    ) {
        $this->stmts = $stmts;
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
                ]),
                [
                    $this->input,
                ],
            ),
        );
    }
}
