<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use Kiboko\Component\ETL\Metadata;
use Phpactor\Docblock\DocblockFactory;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class FieldCopyValueMapperTest extends TestCase
{
    public function automaticMappingDataProvider()
    {
        yield [
            [
                'person' => 'John'
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person]',
            '[employee][first_name]',
        ];

        yield [
            [
                'person' => [
                    'firstName' => 'John',
                ]
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person][firstName]',
            '[employee][first_name]',
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John',
                    ]
                ]
            ],
            [
                'employees' => [
                    [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ]
                ]
            ],
            '[persons][0][firstName]',
            '[employees][0][first_name]',
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John',
                    ]
                ]
            ],
            [
                'employees' => [
                    (function(): \stdClass {
                        $object = new \stdClass;
                        $object->first_name = 'John';
                        $object->last_name = 'Doe';
                        return $object;
                    })()
                ]
            ],
            '[persons][0][firstName]',
            '[employees][0].first_name',
        ];
    }

    /**
     * @dataProvider automaticMappingDataProvider
     */
    public function testStaticResults($expected, $input, $outputField, $inputField)
    {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new FieldCopyValueMapper($outputField, $inputField);

        $this->assertEquals($expected, $staticdMapper($input, []));
    }

    /**
     * @dataProvider automaticMappingDataProvider
     */
    public function testCompilationResults($expected, $input, $outputField, $inputField)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new StandardCompilationContext(
                null,
                null,
                null
            ),
            new FieldCopyValueMapper($outputField, $inputField)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }

    public function configuredMappingDataProvider()
    {
        $typeGuesser = new Metadata\TypeGuesser\CompositeTypeGuesser(
            new Metadata\TypeGuesser\Native\Php74TypeGuesser(),
            new Metadata\TypeGuesser\Docblock\DocblockTypeGuesser(
                (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
                new DocblockFactory()
            )
        );

        $builder = new Metadata\ClassMetadataBuilder(
            new Metadata\PropertyGuesser\ReflectionPropertyGuesser($typeGuesser),
            new Metadata\MethodGuesser\ReflectionMethodGuesser($typeGuesser),
            new Metadata\FieldGuesser\FieldGuesserChain(
                new Metadata\FieldGuesser\PublicPropertyFieldGuesser(),
                new Metadata\FieldGuesser\VirtualFieldGuesser()
            ),
            new Metadata\RelationGuesser\RelationGuesserChain(
                new Metadata\RelationGuesser\PublicPropertyUnaryRelationGuesser(),
                new Metadata\RelationGuesser\PublicPropertyMultipleRelationGuesser(),
                new Metadata\RelationGuesser\VirtualRelationGuesser()
            )
        );
        
        yield [
            [
                'persons' => [
                    (function(): \stdClass {
                        $object = new \stdClass;
                        $object->firstName = 'John';
                        return $object;
                    })()
                ]
            ],
            [
                'employees' => [
                    [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ]
                ]
            ],
            [
                '@input' => new Metadata\ArrayTypeMetadata(
                    new Metadata\ArrayEntryMetadata(
                        'employees',
                        new Metadata\ListTypeMetadata(
                            new Metadata\ArrayTypeMetadata(
                                new Metadata\ArrayEntryMetadata(
                                    'first_name',
                                    new Metadata\ScalarTypeMetadata('string')
                                ),
                                new Metadata\ArrayEntryMetadata(
                                    'last_name',
                                    new Metadata\ScalarTypeMetadata('string')
                                )
                            )
                        )
                    )
                ),
                '@output' => new Metadata\ArrayTypeMetadata(
                    new Metadata\ArrayEntryMetadata(
                        'persons',
                        new Metadata\ListTypeMetadata(
                            $builder->buildFromFQCN(\stdClass::class)
                        )
                    )
                ),
            ],
            '[persons][0].firstName',
            '[employees][0][first_name]',
        ];
    }

    /**
     * @dataProvider configuredMappingDataProvider
     */
    public function testCompilationResultsWithMapping($expected, $input, array $mapping, $outputField, $inputField)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new StandardCompilationContext(
                null,
                null,
                null
            ),
            new FieldCopyValueMapper($outputField, $inputField)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}