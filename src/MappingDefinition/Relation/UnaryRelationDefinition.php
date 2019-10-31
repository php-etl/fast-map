<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class UnaryRelationDefinition implements RelationDefinitionInterface
{
    /** @var string */
    public $name;
    /** @var TypeMetadataInterface[] */
    public $types;

    public function __construct(
        string $name,
        CompositeTypeMetadataInterface ...$types
    ) {
        $this->name = $name;
        $this->types = $types;
    }
}