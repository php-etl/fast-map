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
                        'username' => 'John Gee',
                    ],
                ],
            ],
            []
        ])->shouldReturn([
            'customers' => [
                [
                    'name' => 'John Doe',
                ],
                [
                    'name' => 'Robert Burton',
                ],
                [
                    'name' => 'John Gee',
                ],
            ],
        ]);
    }
}
