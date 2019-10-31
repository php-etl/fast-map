<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;
use PhpSpec\ObjectBehavior;

final class FieldConstantValueMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('[firstName]', 'James');
        $this->shouldHaveType(FieldConstantValueMapper::class);
        $this->shouldHaveType(MapperInterface::class);
        $this->shouldHaveType(CompilableMapperInterface::class);
    }

    function it_is_mapping_flat_data()
    {
        $this->beConstructedWith('[firstName]', 'James');
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            []
        ])->shouldReturn([
            'firstName' => 'James',
        ]);
    }

    function it_is_mapping_complex_data()
    {
        $this->beConstructedWith('[person][firstName]', 'James');
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
                'firstName' => 'James',
            ]
        ]);
    }

    function it_does_keep_preexisting_data()
    {
        $this->beConstructedWith('[person][firstName]', 'James');
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
                'firstName' => 'James',
            ],
        ]);
    }

    function it_is_mapping_flat_data_as_compiled()
    {
        $this->beConstructedWith('[firstName]', 'James');
        $this->compile()->shouldExecuteCompiledTransformation(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [],
            [
                'firstName' => 'James',
            ]
        );
    }

    function it_is_mapping_complex_data_as_compiled()
    {
        $this->beConstructedWith('[person][firstName]', 'James');
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
                    'firstName' => 'James',
                ]
            ]
        );
    }

    function it_does_keep_preexisting_data_as_compiled()
    {
        $this->beConstructedWith('[person][firstName]', 'James');
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
                    'firstName' => 'James',
                ],
            ]
        );
    }
}
