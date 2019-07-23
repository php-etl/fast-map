<?php

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;
use PHPUnit\Framework\TestCase;

class FieldConstantValueMapperTest extends TestCase
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
                    'firstName' => 'John F. Doe',
                ]
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person][firstName]',
            'John F. Doe',
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John F. Doe',
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
            new CompilationContext(
                null,
                null,
                null,
                new FieldConstantValueMapper($outputField, $constantValue)
            )
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}