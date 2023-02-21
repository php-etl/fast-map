<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap;

use Kiboko\Component\FastMap\Compiler\Builder\SimpleObjectInitializerBuilder;
use Kiboko\Contract\Mapping\CompilableObjectInitializerInterface;
use Kiboko\Contract\Metadata\ClassMetadataInterface;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class SimpleObjectInitializer implements CompilableObjectInitializerInterface
{
    /** @var Expression[] */
    private readonly iterable $expressions;
    private readonly PropertyAccessor $accessor;

    public function __construct(
        private readonly ClassMetadataInterface $class,
        private readonly ExpressionLanguage $interpreter,
        Expression ...$expressions
    ) {
        $this->expressions = $expressions;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
            ))->getNode(),
        ];
    }
}
