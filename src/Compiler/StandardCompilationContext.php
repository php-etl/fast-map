<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler;

final class StandardCompilationContext implements CompilationContextInterface
{
    /** @var string|null */
    private $path;
    /**@var string|null */
    private $namespace;
    /** @var string|null */
    private $className;

    public function __construct(?string $path = null, ?string $namespace = null, ?string $className = null)
    {
        $this->path = $path;
        $this->namespace = $namespace;
        $this->className = $className;
    }

    public static function build(string $cachePath, string $fqcn): self
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
            $fileName ?? null,
            $namespace ?? null,
            $className ?? null
        );
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function namespace(): ?string
    {
        return $this->namespace;
    }

    public function className(): ?string
    {
        return $this->className;
    }
}
