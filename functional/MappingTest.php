<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\Metadata\ArgumentListMetadata;
use Kiboko\Component\ETL\Metadata\FieldMetadata;
use Kiboko\Component\ETL\Metadata\FieldGuesser;
use Kiboko\Component\ETL\Metadata\ArgumentMetadata;
use Kiboko\Component\ETL\Metadata\ClassMetadataBuilder;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\ListTypeMetadata;
use Kiboko\Component\ETL\Metadata\MethodGuesser\ReflectionMethodGuesser;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\PropertyGuesser\ReflectionPropertyGuesser;
use Kiboko\Component\ETL\Metadata\PropertyMetadata;
use Kiboko\Component\ETL\Metadata\RelationGuesser;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use Kiboko\Component\ETL\Metadata\TypeGuesser\CompositeTypeGuesser;
use Kiboko\Component\ETL\Metadata\TypeGuesser\Docblock\DocblockTypeGuesser;
use Kiboko\Component\ETL\Metadata\TypeGuesser\Native\Php74TypeGuesser;
use Kiboko\Component\ETL\Metadata\UnaryRelationMetadata;
use Kiboko\Component\ETL\Metadata\VariadicArgumentMetadata;
use Phpactor\Docblock\DocblockFactory;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class MappingTest extends TestCase
{
    public function dataProvider()
    {
        yield [
            (new ClassTypeMetadata('CustomerDTO', 'functional\Kiboko\Component\ETL\FastMap'))
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
                        new ListTypeMetadata(new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap'))
                    ),
                    new PropertyMetadata(
                        'mainAddress',
                        new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                    )
                )
                ->addMethods(
                    new MethodMetadata(
                        'setAddresses',
                        new ArgumentListMetadata(
                            new VariadicArgumentMetadata(
                                'addresses',
                                new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                            )
                        )
                    ),
                    new MethodMetadata(
                        'addAddress',
                        new ArgumentListMetadata(
                            new VariadicArgumentMetadata(
                                'addresses',
                                new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                            )
                        )
                    ),
                    new MethodMetadata(
                        'removeAddress',
                        new ArgumentListMetadata(
                            new VariadicArgumentMetadata(
                                'addresses',
                                new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
                            )
                        )
                    ),
                    new MethodMetadata(
                        'getAddresses',
                        new ArgumentListMetadata(),
                        new ScalarTypeMetadata('iterable'),
                        new ListTypeMetadata(
                            new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
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
                        new ClassReferenceMetadata('AddressDTO', 'functional\Kiboko\Component\ETL\FastMap')
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

        $this->assertEquals($expected, $factory->buildFromFQCN(CustomerDTO::class));
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