<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Mapping\Composite;

use Kiboko\Component\ETL\FastMap\Contracts;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use PhpParser\Node;

final class ArrayMapper implements
    Contracts\ArrayMapperInterface,
    Contracts\CompilableMapperInterface
{
    /** @var Contracts\FieldScopingInterface[] */
    private $fields;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(Contracts\FieldScopingInterface ...$fields)
    {
        $this->fields = $fields;
        $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        foreach ($this->fields as $field) {
            if ($outputPath->getLength() >= 1) {
                try {
                    $initialValue = $this->accessor->getValue($output, $outputPath);
                } catch (NoSuchIndexException|NoSuchPropertyException $exception) {
                    $initialValue = [];
                }

                $this->accessor->setValue(
                    $output,
                    $outputPath,
                    $field($input, $initialValue)
                );
            } else {
                $output = $field($input, $output);
            }
        }

        return $output;
    }

    public function compile(Node\Expr $outputNode): array
    {
        return array_merge(
            ...$this->compileMappers($outputNode)
        );
    }

    private function compileMappers(Node\Expr $outputNode): iterable
    {
        foreach ($this->fields as $mapper) {
            if (!$mapper instanceof Contracts\CompilableInterface) {
                throw new \RuntimeException(strtr(
                    'Expected a %expected%, but got an object of type %actual%.',
                    [
                        '%expected%' => Contracts\CompilableInterface::class,
                        '%actual%' => get_class($mapper),
                    ]
                ));
            }

            yield $mapper->compile($outputNode);
        }
    }
}