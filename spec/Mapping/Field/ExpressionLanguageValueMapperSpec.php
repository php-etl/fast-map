<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping\Field;

use Kiboko\Component\ETL\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\FastMap\Mapping\Field\ExpressionLanguageValueMapper;
use PhpParser\Node;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;

final class ExpressionLanguageValueMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["ipsum"]'));

        $this->shouldHaveType(ExpressionLanguageValueMapper::class);
        $this->shouldHaveType(MapperInterface::class);
        $this->shouldHaveType(CompilableMapperInterface::class);
    }

    function it_is_mapping_flat_data()
    {
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["first_name"]'));

        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [],
            new PropertyPath('[firstName]'),
        ])->shouldReturn([
            'firstName' => 'John',
        ]);
    }

    function it_is_mapping_complex_data()
    {
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["employee"]["first_name"]'));

        $this->callOnWrappedObject('__invoke', [
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            [],
            new PropertyPath('[person][firstName]'),
        ])->shouldReturn([
            'person' => [
                'firstName' => 'John',
            ]
        ]);
    }

    function it_does_keep_preexisting_data()
    {
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["employee"]["first_name"]'));

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
            ],
            new PropertyPath('[person][firstName]'),
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
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["first_name"]'));

        $this->compile((new PropertyPathBuilder(new PropertyPath('[firstName]'), new Node\Expr\Variable('output')))->getNode())
            ->shouldExecuteCompiledMapping(
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
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["employee"]["first_name"]'));

        $this->compile((new PropertyPathBuilder(new PropertyPath('[person][firstName]'), new Node\Expr\Variable('output')))->getNode())
            ->shouldExecuteCompiledMapping(
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
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["employee"]["first_name"]'));

        $this->compile((new PropertyPathBuilder(new PropertyPath('[person][firstName]'), new Node\Expr\Variable('output')))->getNode())
            ->shouldExecuteCompiledMapping(
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
