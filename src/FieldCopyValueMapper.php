<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\ObjectInitialisationPreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\ArrayInitialisationPreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

class FieldCopyValueMapper implements
    Contracts\ArrayMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var string */
    private $outputField;
    /** @var string */
    private $inputField;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(string $outputField, string $inputField)
    {
        $this->outputField = $outputField;
        $this->inputField = $inputField;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output)
    {
        $this->accessor->setValue(
            $output,
            $this->outputField,
            $this->accessor->getValue($input, $this->inputField) ?? null
        );

        return $output;
    }

    public function compile(): array
    {
        $inputPath = new PropertyPath($this->inputField);
        $outputPath = new PropertyPath($this->outputField);

        return array_merge(
            [
                (new RequiredValuePreconditionBuilder($inputPath, new Node\Expr\Variable('input'))),
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
                    (new PropertyPathBuilder($inputPath, new Node\Expr\Variable('input')))->getNode()
                ),
            ]
        );
    }
}