<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping;

use Kiboko\Component\ETL\FastMap\Contracts;
use Kiboko\Component\ETL\FastMap\Mapping;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;

final class RepeatedFieldSpec extends ObjectBehavior
{
    function it_is_initializable(Contracts\MapperInterface $inner)
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new Expression('input["customers." ~ loop.index ~ ".name"]'),
            new Expression('output["users"]'),
            3,
            $inner
        );

        $this->shouldHaveType(Mapping\RepeatedField::class);
        $this->shouldHaveType(Contracts\FieldScopingInterface::class);
        $this->shouldHaveType(Contracts\CompilableInterface::class);
    }

    function it_is_mapping_data()
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            $interpreter,
            new Expression('input["users." ~ loop.index ~ ".username"]'),
            new Expression('output["customers"][loop.index]["name"]'),
            3,
            new Mapping\Field\CopyValueMapper(
                new EmptyPropertyPath()
            )
        );

        $this->shouldExecuteUncompiledMapping(
            [
                'users.0.name' => 'John Doe',
                'users.1.name' => 'Robert Burton',
                'users.2.name' => 'Jane Gee',
            ],
            [
                'customers' => [],
            ],
            [
                'customers' => [
                    [
                        'name' => 'John Doe',
                    ],
                    [
                        'name' => 'Robert Burton',
                    ],
                    [
                        'name' => 'Jane Gee',
                    ],
                ],
            ]
        );
    }

    function it_is_failing_on_invalid_data()
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            $interpreter,
            new Expression('input["users." ~ loop.index ~ ".username"]'),
            new Expression('output["users"][loop.index]["name"]'),
            new Mapping\Field\CopyValueMapper(
                new EmptyPropertyPath()
            )
        );

        $this->shouldThrow(
            new \InvalidArgumentException('The data at path input["users"] in first argument should be iterable.')
        )
            ->during('__invoke', [
                    [
                        'users' => new \StdClass,
                    ],
                    []
                ]
            );
    }
}