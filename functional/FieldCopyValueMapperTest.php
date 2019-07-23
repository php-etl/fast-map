<?php

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use PHPUnit\Framework\TestCase;

class FieldCopyValueMapperTest extends TestCase
{
    public function mappingDataProvider()
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
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testStaticResults($expected, $input, $outputField, $inputField)
    {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new FieldCopyValueMapper($outputField, $inputField);

        $this->assertEquals($expected, $staticdMapper($input, []));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResults($expected, $input, $outputField, $inputField)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new CompilationContext(
                null,
                null,
                null,
                new FieldCopyValueMapper($outputField, $inputField)
            )
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}