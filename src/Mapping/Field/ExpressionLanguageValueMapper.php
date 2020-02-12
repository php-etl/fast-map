<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Field;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\ConstantValueBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\ExpressionLanguageToPhpParserBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ExpressionLanguageValueMapper implements
    Contracts\FieldMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression */
    private $expression;
    /** @var array<string, mixed> */
    private $variables;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        ExpressionLanguage $interpreter,
        Expression $expression,
        array $variables = []
    ) {
        $this->interpreter = $interpreter;
        $this->expression = $expression;
        $this->variables = $variables;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $this->accessor->setValue(
            $output,
            $outputPath,
            $this->interpreter->evaluate($this->expression, array_merge($this->variables, [
                'input' => $input,
                'output' => $output,
            ]))
        );

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return array_merge(
            array_map(function($variable, $value) {
                return new Node\Expr\Assign(
                    new Node\Expr\Variable($variable),
                    (new ConstantValueBuilder($value))->getNode()
                );
            }, array_keys($this->variables), $this->variables),
            [
                new Node\Expr\Assign(
                    $outputNode,
                    (new ExpressionLanguageToPhpParserBuilder($this->interpreter, $this->expression, array_keys($this->variables)))->getNode()
                ),
            ]
        );
    }
}