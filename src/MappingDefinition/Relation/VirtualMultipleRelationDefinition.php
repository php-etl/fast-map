<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class VirtualMultipleRelationDefinition implements RelationDefinitionInterface
{
    /** @var string */
    public $name;
    /** @var TypeMetadataInterface[] */
    public $types;
    /** @var MethodMetadata */
    public $accessor;
    /** @var MethodMetadata */
    public $mutator;
    /** @var MethodMetadata */
    public $adder;
    /** @var MethodMetadata */
    public $remover;
    /** @var MethodMetadata */
    public $walker;
    /** @var MethodMetadata */
    public $counter;

    public function __construct(
        string $name,
        ?MethodMetadata $accessor = null,
        ?MethodMetadata $mutator = null,
        ?MethodMetadata $adder = null,
        ?MethodMetadata $remover = null,
        ?MethodMetadata $walker = null,
        ?MethodMetadata $counter = null,
        TypeMetadataInterface ...$types
    ) {
        $this->name = $name;
        $this->types = $types;
        $this->accessor = $accessor;
        $this->mutator = $mutator;
        $this->adder = $adder;
        $this->remover = $remover;
        $this->walker = $walker;
        $this->counter = $counter;
    }
}