<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class ScopedCodeBuilder implements Builder
{
    /** @var Node\Expr */
    private $input;
    /** @var Node\Expr */
    private $output;
    /** @var Node\Stmt[] */
    private $stmts;

    public function __construct(Node\Expr $input, Node\Expr $output, array $stmts)
    {
        $this->input = $input;
        $this->output = $output;
        $this->stmts = $stmts;
    }

    public function getNode(): Node\Expr
    {
        return new Node\Expr\FuncCall(
            new Node\Name('array_push'),
            [
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
                    ]
                )
            ]
        );
    }
}
