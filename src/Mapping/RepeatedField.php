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

final class RepeatedField implements
    Contracts\FieldScopingInterface,
    Contracts\CompilableInterface
{
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression */
    private $outputExpression;
    /** @var Expression */
    private $inputExpression;
    /** @var Contracts\FieldMapperInterface */
    private $child;
    /** @var PropertyAccessor */
    private $accessor;
    /** @var int */
    private $minimumCount;

    public function __construct(
        ExpressionLanguage $interpreter,
        Expression $outputExpression,
        Expression $inputExpression,
        Contracts\FieldMapperInterface $child,
        int $minimumCount = 1
    ) {
        $this->interpreter = $interpreter;
        $this->outputExpression = $outputExpression;
        $this->inputExpression = $inputExpression;
        $this->child = $child;
        $this->minimumCount = $minimumCount;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        $input = $this->interpreter->evaluate($this->inputExpression, [
            'input' => $input,
            'output' => $output,
        ]);

        if (!is_iterable($input)) {
            throw new \InvalidArgumentException(strtr(
                'The data at path %path% in first argument should be iterable.',
                [
                    '%path%' => $this->inputExpression,
                ]
            ));
        }

        $collection = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);
        $collection->attachIterator((function(iterable $iterable){
            yield from $iterable;
        })($input));
        $collection->attachIterator((function () {
            for ($count = 0; $this->minimumCount > $count; ++$count) {
                yield;
            }
        })());

        foreach ($collection as $index => list($item, $unused)) {
            $outputPath = $this->interpreter->evaluate($this->outputExpression, [
                'input' => $input,
                'output' => $output,
                'loop' => (function(int $index){
                    $loop = new \stdClass();
                    $loop->index = $index;
                    return $loop;
                })($index),
            ]);

            $this->accessor->setValue($output, $outputPath, ($this->child)($item, [], new EmptyPropertyPath()));
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return $this->child->compile((new PropertyPathBuilder($this->path, $outputNode))->getNode());
    }
}