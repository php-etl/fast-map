<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final readonly class ScopedCodeBuilder implements Builder
{
    /**
     * @param \PhpParser\Node\Stmt[] $stmts
     */
    public function __construct(private Node\Expr $input, private Node\Expr $output, private array $stmts)
    {
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
                            ),
                        ],
                        'stmts' => [
                            ...$this->stmts,
                            new Node\Stmt\Return_(new Node\Expr\Variable('output')),
                        ],
                    ]),
                    [
                        new Node\Arg($this->input),
                    ]
                ),
            ]
        );
    }
}
