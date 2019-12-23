<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableObjectInitializerInterface;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
            new $this->className(...$this->walkFields($input))
        );

        return $output;
    }

    private function walkFields(array $input): \Generator
    {
        foreach ($this->inputFields as $field) {
            yield $this->accessor->getValue($input, $field) ?? null;
        }
    }

    /**
     * @return Node[]
     */
    public function compile(): array
    {
        $argumentsNodes = [];
        foreach ($this->expressions as $expression) {
            $compiledExpression = $this->interpreter->parse($expression, ['input', 'output']);

            $argumentsNodes[] = (new ParserFactory())
                ->create(ParserFactory::PREFER_PHP7, null)
                ->parse('<?php ' . $this->interpreter->compile($compiledExpression) . ';');

        }

        return [
            new Node\Stmt\Return_(
                new Node\Expr\New_(
                    new Node\Name\FullyQualified($this->className),
                    $argumentsNodes
                )
            )
        ];
    }
}