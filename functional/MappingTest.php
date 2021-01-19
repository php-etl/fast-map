<?php declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap;

use Kiboko\Component\Metadata\ArgumentListMetadata;
use Kiboko\Component\Metadata\FieldMetadata;
use Kiboko\Component\Metadata\FieldGuesser;
use Kiboko\Component\Metadata\ClassMetadataBuilder;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Component\Metadata\ListTypeMetadata;
use Kiboko\Component\Metadata\MethodGuesser\ReflectionMethodGuesser;
use Kiboko\Component\Metadata\MethodMetadata;
use Kiboko\Component\Metadata\PropertyGuesser\ReflectionPropertyGuesser;
use Kiboko\Component\Metadata\PropertyMetadata;
use Kiboko\Component\Metadata\RelationGuesser;
use Kiboko\Component\Metadata\ScalarTypeMetadata;
use Kiboko\Component\Metadata\TypeGuesser\CompositeTypeGuesser;
use Kiboko\Component\Metadata\TypeGuesser\Docblock\DocblockTypeGuesser;
use Kiboko\Component\Metadata\TypeGuesser\Native\Php74TypeGuesser;
use Kiboko\Component\Metadata\UnaryRelationMetadata;
use Kiboko\Component\Metadata\UnionTypeMetadata;
use Kiboko\Component\Metadata\VariadicArgumentMetadata;
use Phpactor\Docblock\DocblockFactory;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class MappingTest extends TestCase
{
    public function dataProvider()
    {
        yield [
            (new ClassTypeMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'))
                ->addProperties(
                    new PropertyMetadata(
                        'firstName',
                        new ScalarTypeMetadata('string')
                    ),
                    new PropertyMetadata(
                        'lastName',
                        new ScalarTypeMetadata('string')
                    ),
                    new PropertyMetadata(
                        'addresses',
                        new ListTypeMetadata(new ClassReferenceMetadata('Address', namespace\DTO::class))
                    ),
                    new PropertyMetadata(
                        'mainAddress',
                        new ClassReferenceMetadata('Address', namespace\DTO::class)
                    )
                )
                ->addMethods(
                    new MethodMetadata(
                        'setAddresses',
                        new ArgumentListMetadata(
                            new VariadicArgumentMetadata(
                                'addresses',
                                new ClassReferenceMetadata('Address', namespace\DTO::class)
                            )
                        )
                    ),
                    new MethodMetadata(
                        'addAddress',
                        new ArgumentListMetadata(
                            new VariadicArgumentMetadata(
                                'addresses',
                                new ClassReferenceMetadata('Address', namespace\DTO::class)
                            )
                        )
                    ),
                    new MethodMetadata(
                        'removeAddress',
                        new ArgumentListMetadata(
                            new VariadicArgumentMetadata(
                                'addresses',
                                new ClassReferenceMetadata('Address', namespace\DTO::class)
                            )
                        )
                    ),
                    new MethodMetadata(
                        'getAddresses',
                        new ArgumentListMetadata(),
                        new UnionTypeMetadata(
                            new ScalarTypeMetadata('iterable'),
                            new ListTypeMetadata(
                                new ClassReferenceMetadata('Address', namespace\DTO::class)
                            )
                        )
                    )
                )
                ->addFields(
                    new FieldMetadata(
                        'firstName',
                        new ScalarTypeMetadata('string')
                    ),
                    new FieldMetadata(
                        'lastName',
                        new ScalarTypeMetadata('string')
                    )
                )
                ->addRelations(
                    new UnaryRelationMetadata(
                        'mainAddress',
                        new ClassReferenceMetadata('Address', namespace\DTO::class)
                    )
                )
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMapping(ClassTypeMetadata $expected)
    {
        $typeGuesser = new CompositeTypeGuesser(
            new Php74TypeGuesser(),
            new DocblockTypeGuesser(
                (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
                new DocblockFactory()
            )
        );

        $factory = new ClassMetadataBuilder(
            new ReflectionPropertyGuesser($typeGuesser),
            new ReflectionMethodGuesser($typeGuesser),
            new FieldGuesser\FieldGuesserChain(
                new FieldGuesser\PublicPropertyFieldGuesser(),
                new FieldGuesser\VirtualFieldGuesser()
            ),
            new RelationGuesser\RelationGuesserChain(
                new RelationGuesser\PublicPropertyUnaryRelationGuesser(),
                new RelationGuesser\PublicPropertyMultipleRelationGuesser(),
                new RelationGuesser\VirtualRelationGuesser()
            )
        );

        $this->assertEquals($expected, $factory->buildFromFQCN(DTO\Customer::class));
    }
}
