<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use Kiboko\Contract\Metadata\ClassMetadataInterface;
use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SimpleObjectInitializerBuilder implements Builder
{
    /** @var Expression[] */
    private readonly array $expressions;

    public function __construct(
        private readonly ClassMetadataInterface $class,
        private readonly Node\Expr $outputNode,
        private readonly ExpressionLanguage $interpreter,
        Expression ...$expressions
    ) {
        $this->expressions = $expressions;
    }

    public function getNode(): Node
    {
        $argumentsNodes = [];
        foreach ($this->expressions as $expression) {
            array_push(
                $argumentsNodes,
                (new ExpressionLanguageToPhpParserBuilder($this->interpreter, $expression))->getNode()
            );
        }

        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                $this->outputNode,
                new Node\Expr\New_(
                    new Node\Name\FullyQualified((string) $this->class),
                    $argumentsNodes
                )
            )
        );
    }
}
