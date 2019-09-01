<?php

namespace spec\Kiboko\Component\ETL\FastMap;

use Kiboko\Component\ETL\FastMap\CollectionValueMapper;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CollectionValueMapperSpec extends ObjectBehavior
{
    function it_is_initializable(MapperInterface $inner)
    {
        $this->beConstructedWith(
            '[customers]',
            '[users]',
            $inner
        );
        $this->shouldHaveType(CollectionValueMapper::class);
    }

    function it_is_mapping_data()
    {
        $this->beConstructedWith(
            '[customers]',
            '[users]',
            new FieldCopyValueMapper(
                '[name]',
                '[username]'
            )
        );

        $this->callOnWrappedObject('__invoke', [
            [
                'users' => [
                    [
                        'username' => 'John Doe',
                    ],
                    [
                        'username' => 'Robert Burton',
                    ],
                    [
                        'username' => 'Jane Gee',
                    ],
                ],
            ],
            [
                'customers' => [
                    [
                        'email' => 'john@example.com',
                    ],
                    [
                        'email' => 'robert@example.com',
                    ],
                    [
                        'email' => 'jane@example.com',
                    ],
                ],
            ]
        ])->shouldReturn([
            'customers' => [
                [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                ],
                [
                    'email' => 'robert@example.com',
                    'name' => 'Robert Burton',
                ],
                [
                    'email' => 'jane@example.com',
                    'name' => 'Jane Gee',
                ],
            ],
        ]);
    }

    function it_is_failing_on_invalid_dats()
    {
        $this->beConstructedWith(
            '[customers]',
            '[users]',
            new FieldCopyValueMapper(
                '[name]',
                '[username]'
            )
        );

        $this->shouldThrow(
            new \InvalidArgumentException('The data at path [users] in first argument should be iterable.')
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
