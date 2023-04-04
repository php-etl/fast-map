<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler;
use Kiboko\Component\FastMap\Mapping\Field\CopyValueMapper;
use Kiboko\Contract\Mapping\CompiledMapperInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
/**
 * @internal
 *
 * @coversNothing
 */
final class CopyValueMapperTest extends TestCase
{
    public static function mappingDataProvider()
    {
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
            new PropertyPath('[employee][first_name]'),
        ];

        yield [
            [
                'person' => [
                    'firstName' => 'John',
                ],
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            new PropertyPath('[person][firstName]'),
            new PropertyPath('[employee][first_name]'),
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John',
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
            new PropertyPath('[employees][0][first_name]'),
        ];

        yield [
            [
                'persons' => [
                    [
                        'firstName' => 'John',
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
            new PropertyPath('[employees][0].first_name'),
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function dynamicResults(
        mixed $expected,
        mixed $input,
        PropertyPathInterface $outputField,
        PropertyPathInterface $inputField
    ): void {
        /** @var MapperInterface $compiledMapper */
        $staticMapper = new CopyValueMapper($inputField);

        $this->assertEquals($expected, $staticMapper($input, [], $outputField));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mappingDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function compilationResultsWithSpaghettiStrategy(
        mixed $expected,
        mixed $input,
        PropertyPathInterface $outputField,
        PropertyPathInterface $inputField
    ): void {
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
}
