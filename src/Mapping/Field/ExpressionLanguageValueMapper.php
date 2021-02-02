<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\ConstantValueBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\ExpressionLanguageToPhpParserBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ExpressionLanguageValueMapper implements
    Mapping\FieldMapperInterface,
    Mapping\CompilableMapperInterface
{
    /** @var array<string, mixed> */
    private array $variables;
    private PropertyAccessor $accessor;

    public function __construct(
        private ExpressionLanguage $interpreter,
        private Expression $expression,
        array $variables = []
    ) {
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
            array_map(function ($variable, $value) {
                return new Node\Expr\Assign(
                    new Node\Expr\Variable($variable),
                    (new ConstantValueBuilder($value))->getNode()
                );
            }, array_keys($this->variables), $this->variables),
            [
                new Node\Stmt\Expression(
                    new Node\Expr\Assign(
                        $outputNode,
                        (new ExpressionLanguageToPhpParserBuilder($this->interpreter, $this->expression, array_keys($this->variables)))->getNode()
                    ),
                ),
            ]
        );
    }
}
