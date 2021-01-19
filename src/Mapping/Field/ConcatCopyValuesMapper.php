<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Component\FastMap\Contracts;
use PhpParser\BuilderFactory;
use PhpParser\Node;
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
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
            yield $this->accessor->getValue($input, $field) ?? null;
        }
    }

    /**
     * @return Node[]
     */
    public function compile(Node\Expr $outputNode): array
    {
        $inputPaths = array_map(
            function (string $path) {
                return new PropertyPath($path);
            },
            $this->inputPaths
        );

        $nodes = array_map(
            function (PropertyPath $path) {
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
