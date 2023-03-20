<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler;

use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Kiboko\Contract\Mapping\Compiler\CompilationContextInterface;
use Kiboko\Contract\Metadata\ClassMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final readonly class StandardCompilationContext implements CompilationContextInterface
{
    public function __construct(
        private PropertyPathInterface $propertyPath,
        private ?string $path = null,
        private ?ClassMetadataInterface $class = null
    ) {
    }

    public static function build(PropertyPathInterface $propertyPath, string $cachePath, string $fqcn): self
    {
        $fileName = null;
        $className = null;
        if (null !== $fqcn) {
            if (false !== ($index = strrpos($fqcn, '\\'))) {
                $namespace = substr($fqcn, 0, $index);
                $className = substr($fqcn, $index + 1);
            } else {
                $namespace = null;
                $className = $fqcn;
            }
            $fileName = $cachePath.'/'.$className.'.php';
        }

        return new self(
            $propertyPath,
            $fileName,
            empty($className) ? null : new ClassReferenceMetadata($className, $namespace ?? null)
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
        return null !== $this->class ? $this->class->getNamespace() : null;
    }

    public function getClassName(): ?string
    {
        return null !== $this->class ? $this->class->getName() : null;
    }
}
