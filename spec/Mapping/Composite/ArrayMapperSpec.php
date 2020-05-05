<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping\Composite;

use Kiboko\Component\ETL\FastMap\Mapping\Composite\ArrayMapper;
use Kiboko\Component\ETL\FastMap\Mapping\Field;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPath;

final class ArrayMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ArrayMapper::class);
    }

    function it_is_mapping_flat_data()
    {
        $this->beConstructedWith(
            new Field(
                new PropertyPath('[customer][firstName]'),
                new Field\CopyValueMapper(new PropertyPath('[first_name]'))
            )
        );
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [],
            new EmptyPropertyPath(),
        ])->shouldReturn([
            'customer' => [
                'firstName' => 'John',
            ],
        ]);
    }

    function it_is_mapping_flat_data_in_custom_output()
    {
        $this->beConstructedWith(
            new Field(
                new PropertyPath('[customer][firstName]'),
                new Field\CopyValueMapper(new PropertyPath('[first_name]'))
            )
        );
        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [],
            new PropertyPath('[additional]'),
        ])->shouldReturn([
            'additional' => [
                'customer' => [
                    'firstName' => 'John',
                ],
            ],
        ]);
    }

    function it_is_mapping_complex_data()
    {
        $this->beConstructedWith(
            new Field(
                new PropertyPath('[company][person][firstName]'),
                new Field\CopyValueMapper(new PropertyPath('[employee][first_name]')),
            ),
            new Field(
                new PropertyPath('[company][address][city]'),
                new Field\ConcatCopyValuesMapper(' ', new PropertyPath('[address][postcode]'), new PropertyPath('[address][city]'))
            )
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
            [],
            new EmptyPropertyPath(),
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

    function it_is_mapping_complex_data_in_custom_output()
    {
        $this->beConstructedWith(
            new Field(
                new PropertyPath('[company][person][firstName]'),
                new Field\CopyValueMapper(new PropertyPath('[employee][first_name]')),
            ),
            new Field(
                new PropertyPath('[company][address][city]'),
                new Field\ConcatCopyValuesMapper(' ', new PropertyPath('[address][postcode]'), new PropertyPath('[address][city]'))
            )
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
            [],
            new PropertyPath('[additional]'),
        ])->shouldReturn([
            'additional' => [
                'company' => [
                    'person' => [
                        'firstName' => 'John',
                    ],
                    'address' => [
                        'city' => '12345 Oblivion',
                    ],
                ],
            ],
        ]);
    }

    function it_does_keep_preexisting_data()
    {
        $this->beConstructedWith(
            new Field(
                new PropertyPath('[company][person][firstName]'),
                new Field\CopyValueMapper(new PropertyPath('[employee][first_name]'))
            )
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
            new EmptyPropertyPath(),
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

    function it_does_keep_preexisting_data_in_custom_output()
    {
        $this->beConstructedWith(
            new Field(
                new PropertyPath('[company][person][firstName]'),
                new Field\CopyValueMapper(new PropertyPath('[employee][first_name]'))
            )
        );
        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
            [
                'additional' => [
                    'company' => [
                        'address' => [
                            'street' => 'Main Street, 42',
                            'city' => 'Oblivion'
                        ],
                    ],
                ],
            ],
            new PropertyPath('[additional]'),
        ])->shouldReturn([
            'additional' => [
                'company' => [
                    'address' => [
                        'street' => 'Main Street, 42',
                        'city' => 'Oblivion'
                    ],
                    'person' => [
                        'firstName' => 'John',
                    ],
                ],
            ],
        ]);
    }
}
