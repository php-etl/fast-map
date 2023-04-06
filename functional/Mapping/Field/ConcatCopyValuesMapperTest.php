<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler;
use Kiboko\Component\FastMap\Mapping\Field\ConcatCopyValuesMapper;
use Kiboko\Contract\Mapping\CompiledMapperInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ConcatCopyValuesMapperTest extends TestCase
{
    public static function mappingDataProvider()
    {
        yield [
            [
                'person' => 'John Doe',
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person]'),
            ' ',
            new PropertyPath('[employee][first_name]'),
            new PropertyPath('[employee][last_name]'),
        ];

        yield [
            [
                'person' => [
                    'name' => 'John Doe',
                ],
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person][name]'),
            ' ',
            new PropertyPath('[employee][first_name]'),
            new PropertyPath('[employee][last_name]'),
        ];

        yield [
            [
                'persons' => [
                    [
                        'name' => 'John Doe',
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
            ' ',
            new PropertyPath('[employees][0][first_name]'),
            new PropertyPath('[employees][0][last_name]'),
        ];

        yield [
            [
                'persons' => [
                    [
                        'name' => 'John Doe',
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
            ' ',
            new PropertyPath('[employees][0].first_name'),
            new PropertyPath('[employees][0].last_name'),
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    public function testDynamicResults(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        string $glue,
        PropertyPathInterface ...$inputFields
    ): void {
        /** @var MapperInterface $compiledMapper */
        $staticdMapper = new ConcatCopyValuesMapper($glue, ...$inputFields);

        $this->assertEquals($expected, $staticdMapper($input, [], $outputField));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    public function testCompilationResultsWithSpaghettiStrategy(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        string $glue,
        PropertyPathInterface ...$inputFields
    ): void {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Spaghetti());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new ConcatCopyValuesMapper($glue, ...$inputFields)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}
