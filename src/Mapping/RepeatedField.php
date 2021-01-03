<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class RepeatedField implements
    Contracts\FieldScopingInterface,
    Contracts\CompilableInterface
{
    private ExpressionLanguage $interpreter;
    private Expression $outputExpression;
    private Expression $inputExpression;
    private int $repetition;
    private Contracts\MapperInterface $child;

    public function __construct(
        ExpressionLanguage $interpreter,
        Expression $outputExpression,
        Expression $inputExpression,
        int $repetition,
        Contracts\MapperInterface $child
    ) {
        $this->interpreter = $interpreter;
        $this->outputExpression = $outputExpression;
        $this->inputExpression = $inputExpression;
        $this->repetition = $repetition;
        $this->child = $child;
    }

    public function __invoke($input, $output)
    {
        for ($index = 0; $index < $this->repetition; ++$index) {
            $loop = (function($index){
                $loop = new \stdClass();
                $loop->index = $index;
                return $loop;
            })($index);

            $value = $this->interpreter->evaluate($this->inputExpression, [
                'input' => $input,
                'output' => $output,
                'loop' => $loop,
            ]);

            $this->interpreter->evaluate(sprintf('%s = value', $this->outputExpression), [
                'input' => $input,
                'output' => &$output,
                'loop' => $loop,
                'value' => $value,
            ]);
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return $this->child->compile((new PropertyPathBuilder($this->path, $outputNode))->getNode());
    }
}