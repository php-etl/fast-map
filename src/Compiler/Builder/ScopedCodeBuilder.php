<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final class ScopedCodeBuilder implements Builder
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
                        'stmts' => [
                            ...$this->stmts,
                            new Node\Stmt\Return_(new Node\Expr\Variable('output'))
                        ],
                    ]),
                    [
                        new Node\Arg($this->input),
                    ]
                )
            ]
        );
    }
}
