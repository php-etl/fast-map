<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class StandardCompilationContext implements CompilationContextInterface
{
    private PropertyPathInterface $propertyPath;
    private ?string $path;
    private ?string $class;

    public function __construct(PropertyPathInterface $propertyPath, ?string $path = null, ?string $class = null)
    {
        $this->propertyPath = $propertyPath;
        $this->path = $path;
        $this->class = $class;
    }

    public static function build(PropertyPathInterface $propertyPath, string $cachePath, string $fqcn): self
    {
        if ($fqcn !== null) {
            if (false !== ($index = strrpos($fqcn, '\\'))) {
                $namespace = substr($fqcn, 0, $index);
                $className = substr($fqcn, $index + 1);
            } else {
                $namespace = null;
                $className = $fqcn;
            }
            $fileName = $cachePath . '/' . $className . '.php';
        }

        return new self(
            $propertyPath,
            $fileName ?? null,
            !isset($className) ? null : (!isset($namespace) ? $className : sprintf('%s\\%s', $namespace, $className))
        );
    }

    public function getPropertyPath(): PropertyPathInterface
    {
        return $this->propertyPath;
    }

    public function getFilePath(): ?string
    {
        return $this->path;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getNamespace(): ?string
    {
        if ($this->class === null) {
            return null;
        }

        $index = strrpos($this->class, '\\');
        return $index !== false ? substr($this->class, 0, $index) : null;
    }

    public function getClassName(): ?string
    {
        if ($this->class === null) {
            return null;
        }

        $index = strrpos($this->class, '\\');
        return $index !== false ? substr($this->class, $index + 1) : $this->class;
    }
}
