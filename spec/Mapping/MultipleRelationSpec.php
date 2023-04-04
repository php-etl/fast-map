<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\Mapping;

use functional\Kiboko\Component\FastMap\DTO\Customer;
use Kiboko\Component\FastMap\Mapping;
use Kiboko\Component\FastMap\Mapping\Field;
use Kiboko\Component\FastMap\SimpleObjectInitializer;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Kiboko\Contract\Mapping\ObjectMapperInterface;
use PhpParser\Node\Expr\Variable;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;

final class MultipleRelationSpec extends ObjectBehavior
{
    public function it_is_initializable(ObjectMapperInterface $inner): void
    {
        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            new ExpressionLanguage(),
            new Expression('input["users"]'),
            $inner
        );

        $this->shouldHaveType(Mapping\MultipleRelation::class);
    }

    public function it_is_mapping_data(): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            new Mapping\Composite\ObjectMapper(
                new SimpleObjectInitializer(
                    new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'),
                    $interpreter
                ),
                new Field(
                    new PropertyPath('firstName'),
                    new Field\CopyValueMapper(
                        new PropertyPath('[first_name]')
                    )
                ),
                new Field(
                    new PropertyPath('lastName'),
                    new Field\CopyValueMapper(
                        new PropertyPath('[last_name]')
                    )
                ),
                new Field(
                    new PropertyPath('email'),
                    new Field\ExpressionLanguageValueMapper(
                        $interpreter,
                        new Expression('input["email"]')
                    )
                )
            )
        );

        $this->shouldExecuteUncompiledMapping(
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
            [
                'customers' => [
                    (new Customer('John', 'Doe'))->setEmail('john@example.com'),
                    (new Customer('Robert', 'Burton'))->setEmail('robert@example.com'),
                    (new Customer('Jane', 'Gee'))->setEmail('jane@example.com'),
                ],
            ]
        );
    }

    public function it_is_failing_on_invalid_data(): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            new Mapping\Composite\ObjectMapper(
                new SimpleObjectInitializer(
                    new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'),
                    $interpreter
                ),
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

    public function it_is_mapping_data_as_compiled(): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            new Mapping\Composite\ObjectMapper(
                new SimpleObjectInitializer(
                    new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'),
                    $interpreter
                ),
                new Field(
                    new PropertyPath('firstName'),
                    new Field\CopyValueMapper(
                        new PropertyPath('[first_name]')
                    )
                ),
                new Field(
                    new PropertyPath('lastName'),
                    new Field\CopyValueMapper(
                        new PropertyPath('[last_name]')
                    )
                ),
                new Field(
                    new PropertyPath('email'),
                    new Field\ExpressionLanguageValueMapper(
                        $interpreter,
                        new Expression('input["email"]')
                    )
                )
            )
        );

        $this->compile(new Variable('output'))
            ->shouldExecuteCompiledMapping(
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
                [
                    'customers' => [
                        (new Customer('John', 'Doe'))->setEmail('john@example.com'),
                        (new Customer('Robert', 'Burton'))->setEmail('robert@example.com'),
                        (new Customer('Jane', 'Gee'))->setEmail('jane@example.com'),
                    ],
                ]
            )
        ;
    }

    public function it_is_failing_on_invalid_data_while_compiled(): void
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $interpreter,
            new Expression('input["users"]'),
            new Mapping\Composite\ObjectMapper(
                new SimpleObjectInitializer(
                    new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'),
                    $interpreter
                ),
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

        $this->shouldThrowWhenExecuteCompiledMapping(
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
