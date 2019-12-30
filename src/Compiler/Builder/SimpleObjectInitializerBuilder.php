<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use Kiboko\Component\ETL\Metadata\ClassMetadataInterface;
use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SimpleObjectInitializerBuilder implements Builder
{
    /** @var ClassMetadataInterface */
    private $class;
    /** @var Node\Expr */
    private $outputNode;
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression[] */
    private $expressions;

    public function __construct(
        ClassMetadataInterface $class,
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
            array_push($argumentsNodes, ...$this->compileExpression($expression));
        }

        return new Node\Expr\Assign(
            $this->outputNode,
            new Node\Expr\New_(
                new Node\Name\FullyQualified((string) $this->class),
                $argumentsNodes
            )
        );
    }

    private function compileExpression(Expression $expression): iterable
    {
        $compiledExpression = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7)
            ->parse('<?php ' . $this->interpreter->compile($expression, ['input', 'output']) . ';');

        yield from (function(Node\Stmt\Expression ...$expressions) {
            foreach ($expressions as $expression) {
                yield $expression->expr;
            }
        })(...$compiledExpression);
    }
}