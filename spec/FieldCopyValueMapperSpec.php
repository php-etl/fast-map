<?php

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FieldCopyValueMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('lorem', '[ipsum]');
        $this->shouldHaveType(FieldCopyValueMapper::class);
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
}