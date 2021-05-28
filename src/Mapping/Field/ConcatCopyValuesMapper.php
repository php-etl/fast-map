<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Component\FastMap\Mapping\Composite\ArrayMapper;
use Kiboko\Contract\Mapping;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConcatCopyValuesMapper implements
    Mapping\FieldMapperInterface,
    Mapping\CompilableMapperInterface
{
    /** @var string[] */
    private iterable $inputPaths;
    private PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables;

    public function __construct(private string $glue, PropertyPathInterface ...$inputPaths)
    {
        $this->inputPaths = $inputPaths;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->contextVariables = [];
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

    public function addContextVariable(Node\Expr\Variable $variable): ConcatCopyValuesMapper
    {
        $this->contextVariables[] = $variable;

        return $this;
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
                new Node\Stmt\Expression(
                    new Node\Expr\Assign(
                        $outputNode,
                        $factory->concat(...$values)
                    ),
                ),
                new Node\Stmt\Return_(new Node\Expr\Variable('output'))
            ]
        );
    }
}
