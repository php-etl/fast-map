<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final readonly class ExpressionLanguageToPhpParserBuilder implements Builder
{
    /**
     * @param string[] $variables
     */
    public function __construct(private ExpressionLanguage $interpreter, private Expression $expression, private array $variables = [])
    {
    }

    public function getNode(): Node\Expr
    {
        $expression = $this->interpreter->parse($this->expression, array_merge($this->variables, ['input', 'output']));

        $inputNodes = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7, null)
            ->parse('<?php '.$this->interpreter->compile($expression, array_merge($this->variables, ['input', 'output'])).';')
        ;

        /** @var Node\Expr $inputNodes */
        $inputNode = $inputNodes[0];

        return $inputNode->expr;
    }
}
