<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\ArrayCompositeMapper;
use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use PhpSpec\ObjectBehavior;

final class ArrayCompositeMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('[customer]');
        $this->shouldHaveType(ArrayCompositeMapper::class);
    }

    function it_is_mapping_flat_data()
    {
        $this->beConstructedWith(
            '[customer]',
            new FieldCopyValueMapper('[firstName]', '[first_name]')
        );
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            []
        ])->shouldReturn([
            'customer' => [
                'firstName' => 'John',
            ],
        ]);
    }

    function it_is_mapping_complex_data()
    {
        $this->beConstructedWith(
            '[company]',
            new FieldCopyValueMapper('[person][firstName]', '[employee][first_name]'),
            new FieldConcatCopyValuesMapper('[address][city]', ' ', '[address][postcode]', '[address][city]')
        );
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
                'address' => [
                    'street' => 'Main Street, 42',
                    'postcode' => '12345',
                    'city' => 'Oblivion'
                ]
            ],
            []
        ])->shouldReturn([
            'company' => [
                'person' => [
                    'firstName' => 'John',
                ],
                'address' => [
                    'city' => '12345 Oblivion',
                ],
            ],
        ]);
    }

    function it_does_keep_preexisting_data()
    {
        $this->beConstructedWith(
            '[company]',
            new FieldCopyValueMapper('[person][firstName]', '[employee][first_name]')
        );
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            [
                'company' => [
                    'address' => [
                        'street' => 'Main Street, 42',
                        'city' => 'Oblivion'
                    ],
                ],
            ],
        ])->shouldReturn([
            'company' => [
                'address' => [
                    'street' => 'Main Street, 42',
                    'city' => 'Oblivion'
                ],
                'person' => [
                    'firstName' => 'John',
                ],
            ],
        ]);
    }
}
