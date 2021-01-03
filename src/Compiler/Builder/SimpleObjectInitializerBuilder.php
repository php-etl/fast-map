<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SimpleObjectInitializerBuilder implements Builder
{
    /** @var string */
    private $class;
    /** @var Node\Expr */
    private $outputNode;
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression[] */
    private $expressions;

    public function __construct(
        string $class,
        Node\Expr $outputNode,
        ExpressionLanguage $interpreter,
        Expression ...$expressions
    ) {
        $this->class = $class;
        $this->outputNode = $outputNode;
        $this->interpreter = $interpreter;
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

        return new Node\Expr\Assign(
            $this->outputNode,
            new Node\Expr\New_(
                new Node\Name\FullyQualified((string) $this->class),
                $argumentsNodes
            )
        );
    }
}