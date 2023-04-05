<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler;
use Kiboko\Component\FastMap\Mapping\Field\ExpressionLanguageValueMapper;
use Kiboko\Contract\Mapping\CompiledMapperInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ExpressionLanguageValueMapperTest extends TestCase
{
    public static function mappingDataProvider()
    {
        $interpreter = new ExpressionLanguage();

        yield [
            [
                'person' => 'John',
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person]'),
            new Expression('input["employee"]["first_name"]'),
            $interpreter,
        ];

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
            new Expression('input["employee"]["first_name"]~" "~input["employee"]["last_name"]'),
            $interpreter,
        ];

        yield [
            [
                'person' => [
                    'firstName' => 'John Doe',
                ],
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person][firstName]'),
            new Expression('input["employee"]["first_name"]~" "~input["employee"]["last_name"]'),
            $interpreter,
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John Doe',
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
            new PropertyPath('[persons][0][firstName]'),
            new Expression('input["employees"][0]["first_name"]~" "~input["employees"][0]["last_name"]'),
            $interpreter,
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John Doe',
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
            new PropertyPath('[persons][0][firstName]'),
            new Expression('input["employees"][0].first_name~" "~input["employees"][0].last_name'),
            $interpreter,
        ];

        yield [
            [
                'weight' => 2.2675736961451247,
            ],
            [
                'ean' => '1234567890128',
                'weight' => [
                    'value' => 5,
                    'unit' => 'POUNDS',
                ],
                'qty' => 23,
            ],
            new PropertyPath('[weight]'),
            new Expression('input["weight"]["unit"] == "POUNDS" ? (input["weight"]["value"] / 2.205) : input["weight"]["value"]'),
            $interpreter,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    public function testDynamicResults(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        Expression $expression,
        ExpressionLanguage $interpreter
    ): void {
        /** @var MapperInterface $compiledMapper */
        $staticMapper = new ExpressionLanguageValueMapper($interpreter, $expression);

        $this->assertEquals($expected, $staticMapper($input, [], $outputField));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    public function testCompilationResultsWithSpaghettiStrategy(
        $expected,
        $input,
        PropertyPathInterface $outputField,
        Expression $expression,
        ExpressionLanguage $interpreter
    ): void {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Spaghetti());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new ExpressionLanguageValueMapper($interpreter, $expression)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}
