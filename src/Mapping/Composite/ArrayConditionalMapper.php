<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Composite;

use Kiboko\Contract\Mapping;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ArrayConditionalMapper implements
    Mapping\ArrayMapperInterface,
    Mapping\CompilableMapperInterface,
    Mapping\FieldMapperInterface
{
    public function __construct(
        private ExpressionLanguage $interpreter,
        private Expression $condition,
        private ArrayMapper $decorated,
    ) {}

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        if ($this->interpreter->evaluate($this->condition)) {
            return ($this->decorated)($input, $output, $outputPath);
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        $conditionNodes = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7, null)
            ->parse('<?php ' . $this->interpreter->compile($this->condition, ['input', 'output']) . ';');

        return [
            new Node\Stmt\If_(
                cond: $conditionNodes[0],
                subNodes: [
                    $this->decorated->compile($outputNode),
                ]
            ),
        ];
    }
}
