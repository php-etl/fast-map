<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;
use Kiboko\Component\ETL\Metadata;
use PHPUnit\Framework\TestCase;

final class FieldConstantValueMapperTest extends TestCase
{
    public function mappingDataProvider()
    {
        yield [
            [
                'person' => 'John F. Doe'
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person]',
            'John F. Doe',
        ];

        yield [
            [
                'person' => [
                    'name' => 'John F. Doe',
                ]
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person][name]',
            'John F. Doe',
        ];

        yield [
            [
                'persons' => [
                    [
                        'name' => 'John F. Doe',
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
            '[persons][0][name]',
            'John F. Doe',
        ];

        yield [
            [
                'persons' => [
                    [
                        'name' => 'John F. Doe',
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
            '[persons][0][name]',
            'John F. Doe',
        ];
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testStaticResults($expected, $input, $outputField, $constantValue)
    {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new FieldConstantValueMapper($outputField, $constantValue);

        $this->assertEquals($expected, $staticdMapper($input, []));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResults($expected, $input, $outputField, $constantValue)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new StandardCompilationContext(
                null,
                null,
                null
            ),
            new FieldConstantValueMapper($outputField, $constantValue)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }

    public function configuredMappingDataProvider()
    {
        $builder = new Metadata\ClassMetadataBuilder();
        
        yield [
            [
                'persons' => [
                    (function(): \stdClass {
                        $object = new \stdClass;
                        $object->name = 'John F. Doe';
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
            '[persons][0][name]',
            'John F. Doe',
        ];
    }

    /**
     * @dataProvider configuredMappingDataProvider
     */
    public function testCompilationResultsWithMapping($expected, $input, array $mapping, $outputField, $constantValue)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new StandardCompilationContext(
                null,
                null,
                null
            ),
            new FieldConstantValueMapper($outputField, $constantValue)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}