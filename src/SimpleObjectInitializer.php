<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\SimpleObjectInitializerBuilder;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableObjectInitializerInterface;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class SimpleObjectInitializer implements CompilableObjectInitializerInterface
{
    private string $class;
    private ExpressionLanguage $interpreter;
    /** @var Expression[] */
    private iterable $expressions;
    private PropertyAccessorInterface $accessor;

    public function __construct(
        string $class,
        ExpressionLanguage $interpreter,
        Expression ...$expressions
    ) {
        $this->class = $class;
        $this->interpreter = $interpreter;
        $this->expressions = $expressions;
        $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $propertyPath)
    {
        $className = (string) $this->class;
        if ($propertyPath->getLength() >= 1) {
            $this->accessor->setValue(
                $output,
                $propertyPath,
                new $className(...$this->walkFields($input, $output))
            );
        } else {
            $output = new $className(...$this->walkFields($input, $output));
        }

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

    /**
     * @return Node[]
     */
    public function compile(Node\Expr $outputNode): array
    {
        return [
            (new SimpleObjectInitializerBuilder(
                $this->class,
                $outputNode,
                $this->interpreter,
                ...$this->expressions
            ))->getNode()
        ];
    }
}