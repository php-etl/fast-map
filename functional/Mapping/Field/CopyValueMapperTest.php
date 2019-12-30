<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap\Mapping\Field;

use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\Mapping\Field\CopyValueMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class CopyValueMapperTest extends TestCase
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
            new PropertyPath('[person]'),
            new PropertyPath('[employee][first_name]'),
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
            new PropertyPath('[person][firstName]'),
            new PropertyPath('[employee][first_name]'),
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
            new PropertyPath('[persons][0][firstName]'),
            new PropertyPath('[employees][0][first_name]'),
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
            new PropertyPath('[persons][0][firstName]'),
            new PropertyPath('[employees][0].first_name'),
        ];
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testDynamicResults(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        PropertyPathInterface $inputField
    ) {
        /** @var MapperInterface $compiledMapper */
        $staticMapper = new CopyValueMapper($inputField);

        $this->assertEquals($expected, $staticMapper($input, [], $outputField));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResultsWithSpaghettiStrategy(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        PropertyPathInterface $inputField
    ) {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Spaghetti());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new CopyValueMapper($inputField)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResultsWithReduceStrategy(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        PropertyPathInterface $inputField
    ) {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Reduce());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new CopyValueMapper($inputField)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}