<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\ArrayInitialisationPreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

class FieldConcatCopyValuesMapper implements
    Contracts\ArrayMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var string */
    private $outputField;
    /** @var string */
    private $glue;
    /** @var string[] */
    private $inputFields;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(string $outputField, string $glue, string... $inputFields)
    {
        $this->outputField = $outputField;
        $this->glue = $glue;
        $this->inputFields = $inputFields;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        $this->accessor->setValue(
            $output,
            $this->outputField,
            implode($this->glue, iterator_to_array($this->walkFields($input)))
        );

        return $output;
    }

    private function walkFields(array $input): \Generator
    {
        foreach ($this->inputFields as $field) {
            yield $this->accessor->getValue($input, $field) ?? null;
        }
    }

    /**
     * @return Node[]
     */
    public function compile(): array
    {
        $inputPaths = array_map(
            function(string $path) {
                return new PropertyPath($path);
            },
            $this->inputFields
        );
        $outputPath = new PropertyPath($this->outputField);

        $nodes = array_merge(
            array_map(
                function(PropertyPath $path) {
                    return (new RequiredValuePreconditionBuilder($path, new Node\Expr\Variable('input')))->getNode();
                },
                $inputPaths
            ),
            [
                (new ArrayInitialisationPreconditionBuilder($outputPath, $outputNode = new Node\Expr\Variable('output'))),
            ],
            ($count = iterator_count($iterator = $outputPath->getIterator())) > 1 ?
                array_map(
                    function($item) use($outputPath, &$outputNode) {
                        $outputNode = new Node\Expr\ArrayDimFetch(
                            $outputNode,
                            new Node\Scalar\String_($item)
                        );
                        return (new ArrayInitialisationPreconditionBuilder($outputPath, $outputNode))->getNode();
                    },
                    iterator_to_array(new \LimitIterator($iterator, 0, iterator_count($iterator) - 1))
                ) : [],
            [
            ]
        );

        $it = new \ArrayIterator($inputPaths);
        $it->rewind();

        $values = [];
        while ($it->valid()) {
            $values[] = (new PropertyPathBuilder($it->current(), new Node\Expr\Variable('input')))->getNode();

            $it->next();
            if (!$it->valid()) {
                break;
            }

            $values[] = new Node\Scalar\String_($this->glue);
        }

        $factory = new BuilderFactory();

        return array_merge(
            $nodes,
            [
                new Node\Expr\Assign(
                    (new PropertyPathBuilder($outputPath, new Node\Expr\Variable('output')))->getNode(),
                    $factory->concat(...$values)
                )
            ]
        );
    }
}
