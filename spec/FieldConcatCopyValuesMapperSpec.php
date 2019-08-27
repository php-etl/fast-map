<?php

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;
use PhpSpec\ObjectBehavior;

class FieldConcatCopyValuesMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('lorem', ' ', '[ipsum]', '[dolor]');
        $this->shouldHaveType(FieldConcatCopyValuesMapper::class);
    }

    function it_is_mapping_flat_data()
    {
        $this->beConstructedWith('[firstName]', ' ', '[first_name]', '[last_name]');
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            []
        ])->shouldReturn([
            'firstName' => 'John Doe',
        ]);
    }

    function it_is_mapping_complex_data()
    {
        $this->beConstructedWith('[person][firstName]', ' ', '[employee][first_name]', '[employee][last_name]');
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
                'firstName' => 'John Doe',
            ]
        ]);
    }

    function it_does_keep_preexisting_data()
    {
        $this->beConstructedWith('[person][firstName]', ' ', '[employee][first_name]', '[employee][last_name]');
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
                'firstName' => 'John Doe',
            ],
        ]);
    }
}
