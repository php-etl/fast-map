<?php

namespace Kiboko\Component\ETL\FastMap\Compiler;

interface CompilationContextInterface
{
    public function path(): ?string;

    public function namespace(): ?string;

    public function className(): ?string;
}