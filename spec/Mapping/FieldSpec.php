<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping;

use Kiboko\Component\ETL\FastMap\Contracts;
use Kiboko\Component\ETL\FastMap\Mapping;
use PhpParser\Node\Expr\Variable;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPath;

final class FieldSpec extends ObjectBehavior
{
    function it_is_initializable(Contracts\FieldMapperInterface $inner)
    {
        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $inner
        );

        $this->shouldHaveType(Mapping\Field::class);
        $this->shouldHaveType(Contracts\FieldScopingInterface::class);
        $this->shouldHaveType(Contracts\CompilableInterface::class);
    }

    function it_is_mapping_data()
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][name]'),
            new Mapping\Field\CopyValueMapper(
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

    function it_is_mapping_data_as_compiled()
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][name]'),
            new Mapping\Field\CopyValueMapper(
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
            );
    }
}
