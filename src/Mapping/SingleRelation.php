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
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class SingleRelation implements
    Contracts\FieldScopingInterface,
    Contracts\CompilableInterface
{
    /** @var PropertyPath */
    private $outputPath;
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression */
    private $inputExpression;
    /** @var Contracts\ObjectMapperInterface */
    private $child;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        PropertyPathInterface $outputPath,
        ExpressionLanguage $interpreter,
        Expression $inputExpression,
        Contracts\ObjectMapperInterface $child
    ) {
        $this->outputPath = $outputPath;
        $this->interpreter = $interpreter;
        $this->inputExpression = $inputExpression;
        $this->child = $child;
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

        if ($this->outputPath->getLength()) {
            $this->accessor->setValue(
                $output,
                $this->outputPath,
                ($this->child)($input, $output, new EmptyPropertyPath())
            );
        } else {
            $output = ($this->child)($input, $output, new EmptyPropertyPath());
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return array_merge(
            [
//                (new RequiredValuePreconditionBuilder($inputPath, new Node\Expr\Variable('input'))),
                new Node\Expr\Assign(
                    new Node\Expr\Variable('item'),
                    (new PropertyPathBuilder($this->outputPath, new Node\Expr\Variable('input')))->getNode()
                ),
            ],
            $this->child->compile($outputNode)
        );
    }
}