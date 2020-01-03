<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

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

    public function __construct(ExpressionLanguage $interpreter, Expression $expression)
    {
        $this->interpreter = $interpreter;
        $this->expression = $expression;
    }

    public function getNode(): Node
    {
        $expression = $this->interpreter->parse($this->expression, ['input', 'output']);

        $inputNodes = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7, null)
            ->parse('<?php ' . $this->interpreter->compile($expression) . ';');

        return $inputNodes[0]->expr;
    }
}