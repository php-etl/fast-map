<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\Mapping;

use Kiboko\Component\FastMap\Mapping\Field;
use Kiboko\Contract\Mapping\FieldMapperInterface;
use PhpParser\Node\Expr\Variable;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyPath;

final class FieldSpec extends ObjectBehavior
{
    public function it_is_initializable(FieldMapperInterface $inner): void
    {
        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $inner
        );

        $this->shouldHaveType(Field::class);
    }

    public function it_is_mapping_data(): void
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][name]'),
            new Field\CopyValueMapper(
                new PropertyPath('[user][username]')
            )
        );

        $this->shouldExecuteUncompiledMapping(
            [
                'user' => [
                    'username' => 'John Doe',
                ],
            ],
            [
                'customer' => [
                    'email' => 'john@example.com',
                ],
            ],
            [
                'customer' => [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                ],
            ]
        );
    }

    public function it_is_failing_on_invalid_data(): void
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][username]'),
            new Field\CopyValueMapper(
                new PropertyPath('[user][username]')
            )
        );

        $this->shouldThrow(
            new NoSuchIndexException('Cannot read index "username" from object of type "stdClass" because it doesn\'t implement \ArrayAccess.')
        )
            ->during(
                '__invoke',
                [
                    [
                        'user' => new \stdClass(),
                    ],
                    [],
                ]
            )
        ;
    }

    public function it_is_mapping_data_as_compiled(): void
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][name]'),
            new Field\CopyValueMapper(
                new PropertyPath('[user][username]')
            )
        );

        $this->compile(new Variable('output'))
            ->shouldExecuteCompiledMapping(
                [
                    'user' => [
                        'username' => 'John Doe',
                    ],
                ],
                [
                    'customer' => [
                        'email' => 'john@example.com',
                    ],
                ],
                [
                    'customer' => [
                        'email' => 'john@example.com',
                        'name' => 'John Doe',
                    ],
                ]
            )
        ;
    }

    public function it_is_failing_on_invalid_data_while_compiled(): void
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][username]'),
            new Field\CopyValueMapper(
                new PropertyPath('[user][username]')
            )
        );

        $this->shouldThrowWhenExecuteCompiledMapping(
            new NoSuchIndexException('Cannot read index "username" from object of type "stdClass" because it doesn\'t implement \ArrayAccess.')
        )
            ->during(
                '__invoke',
                [
                    [
                        'user' => new \stdClass(),
                    ],
                    [],
                ]
            )
        ;
    }
}
