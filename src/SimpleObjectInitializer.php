<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableObjectInitializerInterface;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

final class SimpleObjectInitializer implements CompilableObjectInitializerInterface
{
    /** @var string */
    private $className;
    /** @var string */
    private $outputField;
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression[] */
    private $expressions;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        string $className,
        string $outputField,
        ExpressionLanguage $interpreter,
        Expression ...$expressions
    ) {
        $this->className = $className;
        $this->outputField = $outputField;
        $this->interpreter = $interpreter;
        $this->expressions = $expressions;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output): object
    {
        $this->accessor->setValue(
            $output,
            $this->outputField,
            new $this->className(...$this->walkFields($input, $output))
        );

        return $output;
    }

    private function walkFields($input, $output): \Generator
    {
        foreach ($this->expressions as $expression) {
            yield $this->interpreter->evaluate($expression, [
                'input' => $input,
                'output' => $output,
            ]);
        }
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

    /**
     * @return Node[]
     */
    public function compile(): array
    {
        $argumentsNodes = [];
        foreach ($this->expressions as $expression) {
            array_push($argumentsNodes, ...$this->compileExpression($expression));
        }

        return array_merge(
            [
                new Node\Expr\Assign(
                    strlen($this->outputField) !== 0 ?
                        (new PropertyPathBuilder(new PropertyPath($this->outputField), new Node\Expr\Variable('output')))->getNode() :
                        new Node\Expr\Variable('output'),
                    new Node\Expr\New_(
                        new Node\Name\FullyQualified($this->className),
                        $argumentsNodes
                    )
                ),
            ]
        );
    }
}