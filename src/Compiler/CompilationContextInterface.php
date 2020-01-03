<?php

namespace Kiboko\Component\ETL\FastMap\Compiler;

use Kiboko\Component\ETL\Metadata\ClassMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface CompilationContextInterface
{
    public function getPropertyPath(): PropertyPathInterface;

    public function getFilePath(): ?string;

    public function getClass(): ?ClassMetadataInterface;
    public function getNamespace(): ?string;
    public function getClassName(): ?string;
}