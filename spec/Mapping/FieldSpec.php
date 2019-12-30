<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping;

use Kiboko\Component\ETL\FastMap\Contracts\FieldMapperInterface;
use Kiboko\Component\ETL\FastMap\Mapping\Field;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyPath;

final class FieldSpec extends ObjectBehavior
{
    function it_is_initializable(FieldMapperInterface $inner)
    {
        $this->beConstructedWith(
            new PropertyPath('[customers]'),
            $inner
        );

        $this->shouldHaveType(Field::class);
    }

    function it_is_mapping_data()
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][name]'),
            new Field\CopyValueMapper(
                new PropertyPath('[user][username]')
            )
        );

        $this->callOnWrappedObject('__invoke', [
            [
                'user' => [
                    'username' => 'John Doe',
                ],
            ],
            [
                'customer' => [
                    'email' => 'john@example.com',
                ],
            ],
        ])->shouldReturn([
            'customer' => [
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ],
        ]);
    }

    function it_is_failing_on_invalid_data()
    {
        $this->beConstructedWith(
            new PropertyPath('[customer][username]'),
            new Field\CopyValueMapper(
                new PropertyPath('[user][username]')
            )
        );

        $this->shouldThrow(
            new NoSuchIndexException('Cannot read index "username" from object of type "stdClass" because it doesn\'t implement \ArrayAccess.')
        )
            ->during('__invoke', [
                [
                    'user' => new \StdClass,
                ],
                []
            ]
        );
    }
}
