<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Mapping\Field;
use Kiboko\Contract\Mapping\CompilableMapperInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use PhpParser\Node;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPath;

final class ConstantValueMapperSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith('James');
        $this->shouldHaveType(Field\ConstantValueMapper::class);
        $this->shouldHaveType(MapperInterface::class);
        $this->shouldHaveType(CompilableMapperInterface::class);
    }

    public function it_is_mapping_flat_data(): void
    {
        $this->beConstructedWith('James');
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [],
            new PropertyPath('[firstName]'),
        ])->shouldReturn([
            'firstName' => 'James',
        ]);
    }

    public function it_is_mapping_complex_data(): void
    {
        $this->beConstructedWith('James');
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            [],
            new PropertyPath('[person][firstName]'),
        ])->shouldReturn([
            'person' => [
                'firstName' => 'James',
            ],
        ]);
    }

    public function it_does_keep_preexisting_data(): void
    {
        $this->beConstructedWith('James');
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            [
                'address' => [
                    'street' => 'Main Street, 42',
                    'city' => 'Oblivion',
                ],
            ],
            new PropertyPath('[person][firstName]'),
        ])->shouldReturn([
            'address' => [
                'street' => 'Main Street, 42',
                'city' => 'Oblivion',
            ],
            'person' => [
                'firstName' => 'James',
            ],
        ]);
    }

    public function it_is_mapping_flat_data_as_compiled(): void
    {
        $this->beConstructedWith('James');

        $this->compile((new PropertyPathBuilder(new PropertyPath('[firstName]'), new Node\Expr\Variable('output')))->getNode())
            ->shouldExecuteCompiledMapping(
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
                [],
                [
                    'firstName' => 'James',
                ]
            )
        ;
    }

    public function it_is_mapping_complex_data_as_compiled(): void
    {
        $this->beConstructedWith('James');

        $this->compile((new PropertyPathBuilder(new PropertyPath('[person][firstName]'), new Node\Expr\Variable('output')))->getNode())
            ->shouldExecuteCompiledMapping(
                [
                    'employee' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                ],
                [],
                [
                    'person' => [
                        'firstName' => 'James',
                    ],
                ]
            )
        ;
    }

    public function it_does_keep_preexisting_data_as_compiled(): void
    {
        $this->beConstructedWith('James');

        $this->compile((new PropertyPathBuilder(new PropertyPath('[person][firstName]'), new Node\Expr\Variable('output')))->getNode())
            ->shouldExecuteCompiledMapping(
                [
                    'employee' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                ],
                [
                    'address' => [
                        'street' => 'Main Street, 42',
                        'city' => 'Oblivion',
                    ],
                ],
                [
                    'address' => [
                        'street' => 'Main Street, 42',
                        'city' => 'Oblivion',
                    ],
                    'person' => [
                        'firstName' => 'James',
                    ],
                ]
            )
        ;
    }
}
