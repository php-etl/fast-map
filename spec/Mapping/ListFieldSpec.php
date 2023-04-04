<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\Mapping;

use Kiboko\Component\FastMap\Mapping;
use Kiboko\Component\FastMap\Mapping\Field;
use Kiboko\Contract\Mapping\ArrayMapperInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;

final class ListFieldSpec extends ObjectBehavior
{
    public function it_is_initializable(ArrayMapperInterface $inner): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            $inner
        );

        $this->shouldHaveType(Mapping\ListField::class);
    }

    public function it_is_mapping_data(): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            new Mapping\Composite\ArrayMapper(
                new Field(
                    new PropertyPath('[name]'),
                    new Field\ConcatCopyValuesMapper(
                        ' ',
                        new PropertyPath('[first_name]'),
                        new PropertyPath('[last_name]')
                    )
                ),
                new Field(
                    new PropertyPath('[email]'),
                    new Field\ExpressionLanguageValueMapper(
                        $interpreter,
                        new Expression('input["email"]')
                    )
                )
            )
        );

        $this->callOnWrappedObject('__invoke', [
            [
                'users' => [
                    [
                        'email' => 'john@example.com',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'username' => 'John Doe',
                    ],
                    [
                        'email' => 'robert@example.com',
                        'first_name' => 'Robert',
                        'last_name' => 'Burton',
                        'username' => 'Robert Burton',
                    ],
                    [
                        'email' => 'jane@example.com',
                        'first_name' => 'Jane',
                        'last_name' => 'Gee',
                        'username' => 'Jane Gee',
                    ],
                ],
            ],
            [
                'customers' => [],
            ],
        ])->shouldReturn([
            'customers' => [
                [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
                [
                    'name' => 'Robert Burton',
                    'email' => 'robert@example.com',
                ],
                [
                    'name' => 'Jane Gee',
                    'email' => 'jane@example.com',
                ],
            ],
        ]);
    }

    public function it_is_failing_on_invalid_data(): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            new Mapping\Composite\ArrayMapper(
                new Field(
                    new PropertyPath('[name]'),
                    new Field\CopyValueMapper(
                        new PropertyPath('[username]')
                    )
                ),
                new Field(
                    new PropertyPath('[email]'),
                    new Field\ExpressionLanguageValueMapper(
                        $interpreter,
                        new Expression('input["email"]')
                    )
                )
            )
        );

        $this->shouldThrow(
            new \InvalidArgumentException('The data at path input["users"] in first argument should be iterable.')
        )
            ->during(
                '__invoke',
                [
                    [
                        'users' => new \stdClass(),
                    ],
                    [],
                ]
            )
        ;
    }
}
