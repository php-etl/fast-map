<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Field;

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
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        ExpressionLanguage $interpreter,
        Expression $expression
    ) {
        $this->interpreter = $interpreter;
        $this->expression = $expression;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $this->accessor->setValue(
            $output,
            $outputPath,
            $this->interpreter->evaluate($this->expression, [
                'input' => $input,
                'output' => $output,
            ])
        );

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return [
            new Node\Expr\Assign(
                $outputNode,
                (new ExpressionLanguageToPhpParserBuilder($this->interpreter, $this->expression))->getNode()
            ),
        ];
    }
}