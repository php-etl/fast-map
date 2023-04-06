<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping;

use Kiboko\Component\FastMap\Compiler\Builder\ExpressionLanguageToPhpParserBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\ScopedCodeBuilder;
use Kiboko\Component\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ListField implements Mapping\FieldScopingInterface, Mapping\CompilableInterface
{
    private readonly PropertyAccessor $accessor;

    public function __construct(
        private PropertyPathInterface $outputPath,
        private readonly ExpressionLanguage $interpreter,
        private readonly Expression $inputExpression,
        private readonly Mapping\CompilableMapperInterface&Mapping\ArrayMapperInterface $child
    ) {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        $input = $this->interpreter->evaluate($this->inputExpression, [
            'input' => $input,
            'output' => $output,
        ]);

        if (!is_iterable($input)) {
            throw new \InvalidArgumentException(strtr('The data at path %path% in first argument should be iterable.', ['%path%' => $this->inputExpression]));
        }

        $collection = $this->accessor->getValue($output, $this->outputPath) ?? [];
        foreach ($input as $item) {
            $collection[] = ($this->child)($item, [], new EmptyPropertyPath());
        }

        $this->accessor->setValue(
            $output,
            $this->outputPath,
            $collection
        );

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return [
            new Node\Stmt\Expression(
                expr: new Node\Expr\Assign(
                    var: (new PropertyPathBuilder($this->outputPath, $outputNode))->getNode(),
                    expr: new Node\Expr\Array_(attributes: ['kind' => Node\Expr\Array_::KIND_SHORT])
                ),
            ),
            new Node\Stmt\Foreach_(
                (new ExpressionLanguageToPhpParserBuilder($this->interpreter, $this->inputExpression))->getNode(),
                new Node\Expr\Variable('item'),
                [
                    'stmts' => [
                        new Node\Stmt\Expression(
                            (new ScopedCodeBuilder(
                                new Node\Expr\Variable('item'),
                                (new PropertyPathBuilder($this->outputPath, $outputNode))->getNode(),
                                $this->child->compile($outputNode),
                            ))->getNode(),
                        ),
                    ],
                ],
            ),
        ];
    }
}
