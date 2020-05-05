<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Field;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Component\ETL\FastMap\Contracts;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConcatCopyValuesMapper implements
    Contracts\FieldMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var string */
    private $glue;
    /** @var string[] */
    private $inputPaths;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(string $glue, PropertyPathInterface ...$inputPaths)
    {
        $this->glue = $glue;
        $this->inputPaths = $inputPaths;
        $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $this->accessor->setValue(
            $output,
            $outputPath,
            implode($this->glue, iterator_to_array($this->walkFields($input)))
        );

        return $output;
    }

    private function walkFields(array $input): \Generator
    {
        foreach ($this->inputPaths as $field) {
            try {
                yield $this->accessor->getValue($input, $field);
            } catch (NoSuchIndexException|NoSuchPropertyException $exception) {
                yield null;
            }
        }
    }

    /**
     * @return Node[]
     */
    public function compile(Node\Expr $outputNode): array
    {
        $inputPaths = array_map(
            function(string $path) {
                return new PropertyPath($path);
            },
            $this->inputPaths
        );

        $nodes = array_map(
            function(PropertyPath $path) {
                return (new RequiredValuePreconditionBuilder($path, new Node\Expr\Variable('input')))->getNode();
            },
            $inputPaths
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
                    $outputNode,
                    $factory->concat(...$values)
                )
            ]
        );
    }
}
