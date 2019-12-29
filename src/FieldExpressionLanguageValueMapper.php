<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\ObjectInitialisationPreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\ArrayInitialisationPreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

final class FieldExpressionLanguageValueMapper implements
    Contracts\ArrayMapperInterface,
    Contracts\ObjectMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var string */
    private $outputField;
    /** @var ExpressionLanguage */
    private $interpreter;
    /** @var Expression */
    private $expression;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(
        string $outputField,
        ExpressionLanguage $interpreter,
        Expression $expression
    ) {
        $this->outputField = $outputField;
        $this->interpreter = $interpreter;
        $this->expression = $expression;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        $this->accessor->setValue(
            $output,
            $this->outputField,
            $this->interpreter->evaluate($this->expression, [
                'input' => $input,
                'output' => $output,
            ])
        );

        return $output;
    }

    public function compile(): array
    {
        $expression = $this->interpreter->parse($this->expression, ['input', 'output']);

        $inputNodes = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7, null)
            ->parse('<?php ' . $this->interpreter->compile($expression) . ';');

        $outputPath = new PropertyPath($this->outputField);

        return array_merge(
            [
                (new ArrayInitialisationPreconditionBuilder($outputPath, $outputNode = new Node\Expr\Variable('output'))),
            ],
            ($count = iterator_count($iterator = $outputPath->getIterator())) > 1 ?
                array_map(
                    function($item) use($iterator, $outputPath, &$outputNode) {
                        if ($iterator->isIndex()) {
                            $outputNode = new Node\Expr\ArrayDimFetch(
                                $outputNode,
                                is_int($item) ? new Node\Scalar\LNumber($item) : new Node\Scalar\String_($item)
                            );
                            return (new ArrayInitialisationPreconditionBuilder($outputPath, $outputNode));
                        }
                        if ($iterator->isProperty()) {
                            $outputNode = new Node\Expr\PropertyFetch(
                                $outputNode,
                                new Node\Name($item)
                            );
                            return (new ObjectInitialisationPreconditionBuilder($outputPath, $outputNode, new ClassTypeMetadata('Baz', 'Foo\\Bar')));
                        }

                        throw new \RuntimeException('Object initialization is not implemented yet.');
                    },
                    iterator_to_array(new \LimitIterator($iterator, 0, iterator_count($iterator) - 1))
                ) : [],
            [
                new Node\Expr\Assign(
                    (new PropertyPathBuilder($outputPath, new Node\Expr\Variable('output')))->getNode(),
                    $inputNodes[0]->expr
                ),
            ]
        );
    }
}