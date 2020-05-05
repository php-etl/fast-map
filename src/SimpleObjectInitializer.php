<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\SimpleObjectInitializerBuilder;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableObjectInitializerInterface;
use Kiboko\Component\ETL\Metadata\ClassMetadataInterface;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class SimpleObjectInitializer implements CompilableObjectInitializerInterface
{
    /** @var ClassMetadataInterface */
    private $class;
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression[] */
    private $expressions;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        ClassMetadataInterface $class,
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