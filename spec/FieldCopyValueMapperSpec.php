<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use PhpSpec\ObjectBehavior;

final class FieldCopyValueMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('lorem', '[ipsum]');
        $this->shouldHaveType(FieldCopyValueMapper::class);
        $this->shouldHaveType(MapperInterface::class);
        $this->shouldHaveType(CompilableMapperInterface::class);
    }

    function it_is_mapping_flat_data()
    {
        $this->beConstructedWith('[firstName]', '[first_name]');
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            []
        ])->shouldReturn([
            'firstName' => 'John',
        ]);
    }

    function it_is_mapping_complex_data()
    {
        $this->beConstructedWith('[person][firstName]', '[employee][first_name]');
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            []
        ])->shouldReturn([
            'person' => [
                'firstName' => 'John',
            ]
        ]);
    }

    function it_does_keep_preexisting_data()
    {
        $this->beConstructedWith('[person][firstName]', '[employee][first_name]');
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            [
                'address' => [
                    'street' => 'Main Street, 42',
                    'city' => 'Oblivion'
                ]
            ]
        ])->shouldReturn([
            'address' => [
                'street' => 'Main Street, 42',
                'city' => 'Oblivion'
            ],
            'person' => [
                'firstName' => 'John',
            ],
        ]);
    }

    function it_is_mapping_flat_data_as_compiled()
    {
        $this->beConstructedWith('[firstName]', '[first_name]');
        $this->compile()->shouldExecuteCompiledTransformation(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [],
            [
                'firstName' => 'John',
            ]
        );
    }

    function it_is_mapping_complex_data_as_compiled()
    {
        $this->beConstructedWith('[person][firstName]', '[employee][first_name]');
        $this->compile()->shouldExecuteCompiledTransformation(
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            [],
            [
                'person' => [
                    'firstName' => 'John',
                ]
            ]
        );
    }

    function it_does_keep_preexisting_data_as_compiled()
    {
        $this->beConstructedWith('[person][firstName]', '[employee][first_name]');
        $this->compile()->shouldExecuteCompiledTransformation(
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            [
                'address' => [
                    'street' => 'Main Street, 42',
                    'city' => 'Oblivion'
                ]
            ],
            [
                'address' => [
                    'street' => 'Main Street, 42',
                    'city' => 'Oblivion'
                ],
                'person' => [
                    'firstName' => 'John',
                ],
            ]
        );
    }
}
