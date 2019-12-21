<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use Kiboko\Component\ETL\FastMap\FieldExpressionLanguageValueMapper;
use Kiboko\Component\ETL\Metadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

final class FieldExpressionLanguageValueMapperTest extends TestCase
{
    public function automaticMappingDataProvider()
    {
        $interpreter = new ExpressionLanguage();

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
            new Expression('input["employee"]["first_name"]'),
            $interpreter,
        ];

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
            new Expression('input["employee"]["first_name"]~" "~input["employee"]["last_name"]'),
            $interpreter,
        ];

        yield [
            [
                'person' => [
                    'firstName' => 'John Doe',
                ]
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            '[person][firstName]',
            new Expression('input["employee"]["first_name"]~" "~input["employee"]["last_name"]'),
            $interpreter,
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John Doe',
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
            new Expression('input["employees"][0]["first_name"]~" "~input["employees"][0]["last_name"]'),
            $interpreter,
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John Doe',
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
            new Expression('input["employees"][0].first_name~" "~input["employees"][0].last_name'),
            $interpreter,
        ];

        yield [
            [
                'weight' => 2.2675736961451247
            ],
            [
                'ean' => '1234567890128',
                'weight' => [
                    'value' => 5,
                    'unit' => 'POUNDS',
                ],
                'qty' => 23,
            ],
            '[weight]',
            new Expression('input["weight"]["unit"] == "POUNDS" ? (input["weight"]["value"] / 2.205) : input["weight"]["value"]'),
            $interpreter
        ];
    }

    /**
     * @dataProvider automaticMappingDataProvider
     */
    public function testStaticResults($expected, $input, $outputField, Expression $expression, ExpressionLanguage $interpreter)
    {
        /** @var MapperInterface $compiledMapper */
        $staticMapper = new FieldExpressionLanguageValueMapper($outputField, $expression, $interpreter);

        $this->assertEquals($expected, $staticMapper($input, []));
    }

    /**
     * @dataProvider automaticMappingDataProvider
     */
    public function testCompilationResults($expected, $input, $outputField, Expression $expression, ExpressionLanguage $interpreter)
    {
        $compiler = new Compiler();

        /** @var MapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new StandardCompilationContext(
                null,
                null,
                null
            ),
            new FieldExpressionLanguageValueMapper($outputField, $expression, $interpreter)
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}