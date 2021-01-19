<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler;

use Kiboko\Component\Metadata\ClassMetadataInterface;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class StandardCompilationContext implements CompilationContextInterface
{
    /** @var PropertyPathInterface */
    private $propertyPath;
    /** @var string|null */
    private $path;
    /** @var ClassMetadataInterface|null */
    private $class;

    public function __construct(PropertyPathInterface $propertyPath, ?string $path = null, ?ClassMetadataInterface $class = null)
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
            !isset($className) ? null : new ClassReferenceMetadata($className, $namespace ?? null)
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

    public function getClass(): ?ClassMetadataInterface
    {
        return $this->class;
    }

    public function getNamespace(): ?string
    {
        return $this->class !== null ? $this->class->getNamespace() : null;
    }

    public function getClassName(): ?string
    {
        return $this->class !== null ? $this->class->getName() : null;
    }
}
