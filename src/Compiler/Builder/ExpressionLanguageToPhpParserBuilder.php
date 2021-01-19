<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionLanguageToPhpParserBuilder implements Builder
{
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression */
    private $expression;
    /** @var array<string> */
    private $variables;

    public function __construct(ExpressionLanguage $interpreter, Expression $expression, array $variables = [])
    {
        $this->interpreter = $interpreter;
        $this->expression = $expression;
        $this->variables = $variables;
    }

    public function getNode(): Node
    {
        $expression = $this->interpreter->parse($this->expression, array_merge($this->variables, ['input', 'output']));

        $inputNodes = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7, null)
            ->parse('<?php ' . $this->interpreter->compile($expression, array_merge($this->variables, ['input', 'output'])) . ';');

        return $inputNodes[0]->expr;
    }
}
