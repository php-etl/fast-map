<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingIteration;

use Kiboko\Component\ETL\FastMap\MappingIteration\ClassMappingIterator;
use Kiboko\Component\ETL\Metadata\ArgumentListMetadata;
use Kiboko\Component\ETL\Metadata\ArgumentMetadata;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\FieldMetadata;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\PropertyMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use Kiboko\Component\ETL\Metadata\UnaryRelationMetadata;
use Kiboko\Component\ETL\Metadata\VoidTypeMetadata;
use PhpSpec\ObjectBehavior;

final class ClassMappingIteratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(ClassMappingIterator::class);
        $this->beAnInstanceOf(\RecursiveIterator::class);
    }

    function it_should_not_iterate_properties()
    {
        $metadata = new ClassTypeMetadata('Lorem', 'Ipsum');
        $metadata->addProperties(
            new PropertyMetadata('lorem', new ScalarTypeMetadata('string')),
            new PropertyMetadata('ipsum', new ScalarTypeMetadata('string'))
        );

        $this->beConstructedWith($metadata);

        $this->shouldIterateLike(new \ArrayIterator([]));
    }

    function it_finds_fields()
    {
        $metadata = new ClassTypeMetadata('Lorem', 'Ipsum');
        $metadata->addProperties(
            new PropertyMetadata('lorem', new ScalarTypeMetadata('string')),
            new PropertyMetadata('ipsum', new ScalarTypeMetadata('string'))
        );
        $metadata->addFields(
            new FieldMetadata('lorem', new ScalarTypeMetadata('string')),
            new FieldMetadata('ipsum', new ScalarTypeMetadata('string'))
        );

        $this->beConstructedWith($metadata);

        $this->shouldIterateLike(new \ArrayIterator([
            'lorem' => new FieldMetadata('lorem', new ScalarTypeMetadata('string')),
            'ipsum' => new FieldMetadata('ipsum', new ScalarTypeMetadata('string')),
        ]));
    }

    function it_should_not_iterate_methods()
    {
        $metadata = new ClassTypeMetadata('Lorem', 'Ipsum');
        $metadata->addMethods(
            new MethodMetadata('getLorem', new ArgumentListMetadata(), new ScalarTypeMetadata('string')),
            new MethodMetadata('setLorem', new ArgumentListMetadata(new ArgumentMetadata('lorem', new ScalarTypeMetadata('string'))), new VoidTypeMetadata()),
            new MethodMetadata('getIpsum', new ArgumentListMetadata(), new ClassReferenceMetadata('Ipsum', 'Lorem')),
            new MethodMetadata('setIpsum', new ArgumentListMetadata(new ArgumentMetadata('ipsum', new ClassReferenceMetadata('Ipsum', 'Lorem'))), new VoidTypeMetadata())
        );

        $this->beConstructedWith($metadata);

        $this->shouldIterateLike(new \ArrayIterator([]));
    }

    function it_finds_virtual_fields_and_relations()
    {
        $metadata = new ClassTypeMetadata('Lorem', 'Ipsum');
        $metadata->addMethods(
            new MethodMetadata('getLorem', new ArgumentListMetadata(), new ScalarTypeMetadata('string')),
            new MethodMetadata('setLorem', new ArgumentListMetadata(new ArgumentMetadata('lorem', new ScalarTypeMetadata('string'))), new VoidTypeMetadata()),
            new MethodMetadata('getIpsum', new ArgumentListMetadata(), new ClassReferenceMetadata('Ipsum', 'Lorem')),
            new MethodMetadata('setIpsum', new ArgumentListMetadata(new ArgumentMetadata('ipsum', new ClassReferenceMetadata('Ipsum', 'Lorem'))), new VoidTypeMetadata())
        );
        $metadata->addFields(
            new FieldMetadata('lorem', new ScalarTypeMetadata('string'))
        );
        $metadata->addRelations(
            new UnaryRelationMetadata('ipsum', new ClassReferenceMetadata('Ipsum', 'Lorem'))
        );

        $this->beConstructedWith($metadata);

        $this->shouldIterateLike(new \ArrayIterator([
            'lorem' => new FieldMetadata('lorem', new ScalarTypeMetadata('string')),
            'ipsum' => new UnaryRelationMetadata('ipsum', new ClassReferenceMetadata('Ipsum', 'Lorem')),
        ]));
    }
}