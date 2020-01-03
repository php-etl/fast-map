<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap\Mapping\Field;

use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\Mapping\Field\ConstantValueMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConstantValueMapperTest extends TestCase
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
            new PropertyPath('[person]'),
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
            new PropertyPath('[person][name]'),
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
            new PropertyPath('[persons][0][name]'),
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
            new PropertyPath('[persons][0][name]'),
            'John F. Doe',
        ];
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testDynamicResults(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        string $constantValue
    ) {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new ConstantValueMapper($constantValue);

        $this->assertEquals($expected, $staticdMapper($input, [], $outputField));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResultsWithSpaghettiStrategy(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        string $constantValue
    ) {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Spaghetti());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new ConstantValueMapper($constantValue)
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
        string $constantValue
    ) {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Reduce());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new ConstantValueMapper($constantValue)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}