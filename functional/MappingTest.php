<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinition;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\FieldDefinitionGuesserChain;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\PublicPropertyFieldGuesser;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\PublicPropertyUnaryRelationGuesser;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\RelationDefinitionGuesserChain;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\VirtualFieldGuesser;
use Kiboko\Component\ETL\FastMap\MappingDefinition\MappedClassType;
use Kiboko\Component\ETL\FastMap\MappingDefinition\MappedClassTypeFactory;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\UnaryRelationDefinition;
use Kiboko\Component\ETL\Metadata\ArgumentMetadata;
use Kiboko\Component\ETL\Metadata\ArgumentMetadataList;
use Kiboko\Component\ETL\Metadata\ClassMetadataBuilder;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\ListTypeMetadata;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\PropertyMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use PHPUnit\Framework\TestCase;

final class MappingTest extends TestCase
{
    public function dataProvider()
    {
        yield [
            (new MappedClassType(
                (new ClassTypeMetadata('CustomerDTO', 'functional\Kiboko\Component\ETL\FastMap'))
                    ->properties(
                        new PropertyMetadata(
                            'firstName',
                            new ScalarTypeMetadata('string')
                        ),
                        new PropertyMetadata(
                            'lastName',
                            new ScalarTypeMetadata('string')
                        ),
                        new PropertyMetadata(
                            'mainAddress',
                            new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                        )
                    )
                    ->methods(
                        new MethodMetadata(
                            'setAddresses',
                            new ArgumentMetadataList(
                                new ArgumentMetadata(
                                    'addresses',
                                    true,
                                    new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                                )
                            )
                        ),
                        new MethodMetadata(
                            'addAddress',
                            new ArgumentMetadataList(
                                new ArgumentMetadata(
                                    'addresses',
                                    true,
                                    new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                                )
                            )
                        ),
                        new MethodMetadata(
                            'removeAddress',
                            new ArgumentMetadataList(
                                new ArgumentMetadata(
                                    'addresses',
                                    true,
                                    new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                                )
                            )
                        ),
                        new MethodMetadata(
                            'getAddresses',
                            new ArgumentMetadataList(),
                            new ScalarTypeMetadata('iterable'),
                            new ListTypeMetadata(
                                new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                            )
                        )
                    )
            ))
                ->fields(
                    new FieldDefinition(
                        'firstName',
                        new ScalarTypeMetadata('string')
                    ),
                    new FieldDefinition(
                        'lastName',
                        new ScalarTypeMetadata('string')
                    )
                )
                ->relations(
                    new UnaryRelationDefinition(
                        'mainAddress',
                        new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                    )
                ),
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMapping(MappedClassType $expected)
    {
        $metadataBuilder = new ClassMetadataBuilder();
        $factory = new MappedClassTypeFactory(
            new FieldDefinitionGuesserChain(
                new PublicPropertyFieldGuesser(),
                new VirtualFieldGuesser()
            ),
            new RelationDefinitionGuesserChain(
                new PublicPropertyUnaryRelationGuesser()/*,
                new PublicPropertyMultipleRelationGuesser(),
                new VirtualRelationGuesser()*/
            )
        );

        $this->assertEquals($expected, $factory($metadataBuilder->buildFromFQCN(CustomerDTO::class)));
    }
}

final class CustomerDTO
{
    public string $firstName;
    public string $lastName;
    public AddressDTO $mainAddress;
    /** @var AddressDTO[] */
    private array $addresses;

    public function setAddresses(AddressDTO ...$addresses)
    {
        $this->addresses = $addresses;
    }

    public function addAddress(AddressDTO ...$addresses)
    {
        $this->addresses = array_merge(
            $this->addresses,
            $addresses
        );
    }

    public function removeAddress(AddressDTO ...$addresses)
    {
        $this->addresses = array_diff(
            $this->addresses,
            $addresses
        );
    }

    /** @return AddressDTO[] */
    public function getAddresses(): iterable
    {
        return $this->addresses;
    }
}

final class AddressDTO
{
    public string $name;
    public string $street;
    public string $city;
}