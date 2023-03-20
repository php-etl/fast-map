<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap;

use Kiboko\Component\Metadata\ArgumentListMetadata;
use Kiboko\Component\Metadata\ArgumentMetadata;
use Kiboko\Component\Metadata\ClassMetadataBuilder;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Component\Metadata\FieldGuesser;
use Kiboko\Component\Metadata\ListTypeMetadata;
use Kiboko\Component\Metadata\MethodGuesser\ReflectionMethodGuesser;
use Kiboko\Component\Metadata\MethodMetadata;
use Kiboko\Component\Metadata\MixedTypeMetadata;
use Kiboko\Component\Metadata\NullTypeMetadata;
use Kiboko\Component\Metadata\PropertyGuesser\ReflectionPropertyGuesser;
use Kiboko\Component\Metadata\PropertyMetadata;
use Kiboko\Component\Metadata\RelationGuesser;
use Kiboko\Component\Metadata\ScalarTypeMetadata;
use Kiboko\Component\Metadata\TypeGuesser\CompositeTypeGuesser;
use Kiboko\Component\Metadata\TypeGuesser\Docblock\DocblockTypeGuesser;
use Kiboko\Component\Metadata\TypeGuesser\Native\NativeTypeGuesser;
use Kiboko\Component\Metadata\UnionTypeMetadata;
use Kiboko\Component\Metadata\VariadicArgumentMetadata;
use Kiboko\Component\Metadata\VirtualFieldMetadata;
use Kiboko\Component\Metadata\VirtualUnaryRelationMetadata;
use Phpactor\Docblock\DocblockFactory;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
/**
 * @internal
 *
 * @coversNothing
 */
final class MappingTest extends TestCase
{
    public static function dataProvider()
    {
        yield [
            (new ClassTypeMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'))
                ->addProperties(
                    new PropertyMetadata(
                        'firstName',
                        new UnionTypeMetadata(
                            new ScalarTypeMetadata('string'),
                            new NullTypeMetadata(),
                        )
                    ),
                    new PropertyMetadata(
                        'lastName',
                        new UnionTypeMetadata(
                            new ScalarTypeMetadata('string'),
                            new NullTypeMetadata(),
                        )
                    ),
                    new PropertyMetadata(
                        'email',
                        new UnionTypeMetadata(
                            new ScalarTypeMetadata('string'),
                            new NullTypeMetadata(),
                        )
                    ),
                    new PropertyMetadata(
                        'mainAddress',
                        new UnionTypeMetadata(
                            new ClassReferenceMetadata('Address', namespace\DTO::class),
                            new NullTypeMetadata(),
                        )
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
                    ),
                    new MethodMetadata(
                        'setEmail',
                        new ArgumentListMetadata(
                            new ArgumentMetadata(
                                'email',
                                new ScalarTypeMetadata('string')
                            )
                        ),
                        returnType: new ClassReferenceMetadata(
                            'Customer',
                            'functional\Kiboko\Component\FastMap\DTO'
                        )
                    ),
                    new MethodMetadata(
                        '__construct',
                        new ArgumentListMetadata(
                            new ArgumentMetadata(
                                'firstName',
                                new UnionTypeMetadata(
                                    new ScalarTypeMetadata('string'),
                                    new NullTypeMetadata()
                                )
                            ),
                            new ArgumentMetadata(
                                'lastName',
                                new UnionTypeMetadata(
                                    new ScalarTypeMetadata('string'),
                                    new NullTypeMetadata()
                                )
                            ),
                            new ArgumentMetadata(
                                'mainAddress',
                                new UnionTypeMetadata(
                                    new ClassReferenceMetadata(
                                        'Address',
                                        'functional\Kiboko\Component\FastMap\DTO'
                                    ),
                                    new NullTypeMetadata()
                                )
                            )
                        ),
                        returnType: new MixedTypeMetadata()
                    )
                )
                ->addFields(
                    new VirtualFieldMetadata(
                        'email',
                        new ScalarTypeMetadata('string'),
                        mutator: new MethodMetadata(
                            'setEmail',
                            new ArgumentListMetadata(
                                new ArgumentMetadata(
                                    'email',
                                    new ScalarTypeMetadata('string')
                                )
                            ),
                            returnType: new ClassReferenceMetadata(
                                'Customer',
                                'functional\Kiboko\Component\FastMap\DTO'
                            ),
                        )
                    ),
                )
                ->addRelations(
                    new VirtualUnaryRelationMetadata(
                        'email',
                        type: new ClassReferenceMetadata('Customer', namespace\DTO::class),
                        mutator: new MethodMetadata(
                            'setEmail',
                            new ArgumentListMetadata(
                                new ArgumentMetadata(
                                    'email',
                                    new ScalarTypeMetadata('string')
                                )
                            ),
                            returnType: new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO')
                        )
                    ),
                ),
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mapping(ClassTypeMetadata $expected): void
    {
        $typeGuesser = new CompositeTypeGuesser(
            new NativeTypeGuesser(),
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
