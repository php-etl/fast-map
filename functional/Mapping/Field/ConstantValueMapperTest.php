<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler;
use Kiboko\Component\FastMap\Mapping\Field\ConstantValueMapper;
use Kiboko\Contract\Mapping\CompiledMapperInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConstantValueMapperTest extends TestCase
{
    public static function mappingDataProvider()
    {
        yield [
            [
                'person' => 'John F. Doe',
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person]'),
            'John F. Doe',
        ];

        yield [
            [
                'person' => [
                    'name' => 'John F. Doe',
                ],
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person][name]'),
            'John F. Doe',
        ];

        yield [
            [
                'persons' => [
                    [
                        'name' => 'John F. Doe',
                    ],
                ],
            ],
            [
                'employees' => [
                    [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                ],
            ],
            new PropertyPath('[persons][0][name]'),
            'John F. Doe',
        ];

        yield [
            [
                'persons' => [
                    [
                        'name' => 'John F. Doe',
                    ],
                ],
            ],
            [
                'employees' => [
                    (function (): \stdClass {
                        $object = new \stdClass();
                        $object->first_name = 'John';
                        $object->last_name = 'Doe';

                        return $object;
                    })(),
                ],
            ],
            new PropertyPath('[persons][0][name]'),
            'John F. Doe',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    public function testDynamicResults(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        string $constantValue
    ): void {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new ConstantValueMapper($constantValue);

        $this->assertEquals($expected, $staticdMapper($input, [], $outputField));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    public function testCompilationResultsWithSpaghettiStrategy(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        string $constantValue
    ): void {
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
}
