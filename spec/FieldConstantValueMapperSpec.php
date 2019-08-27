<?php

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FieldConstantValueMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('[firstName]', 'James');
        $this->shouldHaveType(FieldConstantValueMapper::class);
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
}
