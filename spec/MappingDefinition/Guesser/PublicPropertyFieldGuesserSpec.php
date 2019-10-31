<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinition;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\FieldDefinitionGuesserInterface;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\PublicPropertyFieldGuesser;
use Kiboko\Component\ETL\Metadata\ArrayTypeMetadata;
use Kiboko\Component\ETL\Metadata\ClassMetadataBuilder;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\PropertyMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use PhpSpec\ObjectBehavior;

final class PublicPropertyFieldGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PublicPropertyFieldGuesser::class);
        $this->shouldHaveType(FieldDefinitionGuesserInterface::class);
    }

    function it_is_discovering_properties()
    {
        $metadata = (new ClassMetadataBuilder())->buildFromObject(new class {
            /** @var string */
            public $foo;
            public $foz;
            public \stdClass $object;
            protected $bar;
            private $baz;
        });

        $this->__invoke($metadata)
            ->shouldIterateLike(new \ArrayIterator([
                new FieldDefinition('foo', new ScalarTypeMetadata('string')),
            ]))
        ;
    }
}