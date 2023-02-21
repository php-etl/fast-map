<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\ExpressionLanguageToPhpParserBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ExpressionLanguageValueMapper implements Mapping\FieldMapperInterface, Mapping\CompilableMapperInterface
{
    private readonly PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables = [];

    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(
        private readonly ExpressionLanguage $interpreter,
        private readonly Expression $expression,
        private readonly array $variables = []
    ) {
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

    public function addContextVariable(Node\Expr\Variable $variable): self
    {
        $this->contextVariables[] = $variable;

        return $this;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return [
            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    $outputNode,
                    (new ExpressionLanguageToPhpParserBuilder($this->interpreter, $this->expression, array_keys($this->variables)))->getNode()
                ),
            ),
        ];
    }
}
