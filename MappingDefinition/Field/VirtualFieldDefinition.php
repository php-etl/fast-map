<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Field;

use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\TypeMetadata;

class VirtualFieldDefinition extends FieldDefinition
{
    /** @var MethodMetadata */
    public $accessor;
    /** @var MethodMetadata */
    public $mutator;
    /** @var MethodMetadata */
    public $checker;
    /** @var MethodMetadata */
    public $remover;

    public function __construct(
        string $name,
        ?MethodMetadata $accessor = null,
        ?MethodMetadata $mutator = null,
        ?MethodMetadata $checker = null,
        ?MethodMetadata $remover = null,
        TypeMetadata ...$types
    ) {
        parent::__construct($name, ...$types);
        $this->accessor = $accessor;
        $this->mutator = $mutator;
        $this->checker = $checker;
        $this->remover = $remover;
    }
}