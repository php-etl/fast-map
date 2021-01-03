<?php

namespace Kiboko\Component\ETL\FastMap\Compiler;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface CompilationContextInterface
{
    public function getPropertyPath(): PropertyPathInterface;

    public function getFilePath(): ?string;

    public function getClass(): ?string;
    public function getNamespace(): ?string;
    public function getClassName(): ?string;
}