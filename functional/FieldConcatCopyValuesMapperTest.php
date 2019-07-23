<?php

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use PHPUnit\Framework\TestCase;

class FieldConcatCopyValuesMapperTest extends TestCase
{
    public function mappingDataProvider()
    {
        yield [
            [
                'person' => 'John Doe'
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person]',
            ' ',
            '[employee][first_name]',
            '[employee][last_name]',
        ];

        yield [
            [
                'person' => [
                    'name' => 'John Doe',
                ]
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person][name]',
            ' ',
            '[employee][first_name]',
            '[employee][last_name]',
        ];

        yield [
            [
                'person' => [
                    'name' => 'John Doe',
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
            '[person][name]',
            ' ',
            '[employees][0][first_name]',
            '[employees][0][last_name]',
        ];
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testStaticResults($expected, $input, $outputField, $glue, ...$inputFields)
    {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new FieldConcatCopyValuesMapper($outputField, $glue, ...$inputFields);

        $this->assertEquals($expected, $staticdMapper($input, []));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResults($expected, $input, $outputField, $glue, ...$inputFields)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new CompilationContext(
                null,
                null,
                null,
                new FieldConcatCopyValuesMapper($outputField, $glue, ...$inputFields)
            )
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}