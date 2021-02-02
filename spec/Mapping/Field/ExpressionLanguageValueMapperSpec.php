<?php declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Mapping\Field\ExpressionLanguageValueMapper;
use Kiboko\Contract\Mapping\CompilableMapperInterface;
use Kiboko\Contract\Mapping\MapperInterface;
use PhpParser\Node;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;

final class ExpressionLanguageValueMapperSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(new ExpressionLanguage(), new Expression('input["ipsum"]'));

        $this->shouldHaveType(ExpressionLanguageValueMapper::class);
        $this->shouldHaveType(MapperInterface::class);
        $this->shouldHaveType(CompilableMapperInterface::class);
    }

    public function it_is_mapping_flat_data()
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

    public function it_is_mapping_complex_data()
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

    public function it_does_keep_preexisting_data()
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

    public function it_is_mapping_complex_data_with_additional_variables()
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new Expression('input["employee"]["first_name"] ~" - "~ lorem'),
            [
                'lorem' => 'Lorem ipsum dolor sit amet consecutir',
            ]
        );

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
                'firstName' => 'John - Lorem ipsum dolor sit amet consecutir',
            ]
        ]);
    }

    public function it_does_keep_preexisting_data_with_additional_variables()
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new Expression('input["employee"]["first_name"] ~" - "~ lorem'),
            [
                'lorem' => 'Lorem ipsum dolor sit amet consecutir',
            ]
        );

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
                'firstName' => 'John - Lorem ipsum dolor sit amet consecutir',
            ],
        ]);
    }

    public function it_is_mapping_flat_data_as_compiled()
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

    public function it_is_mapping_complex_data_as_compiled()
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

    public function it_does_keep_preexisting_data_as_compiled()
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

    public function it_is_mapping_complex_data_with_additional_variables_as_compiled()
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new Expression('input["employee"]["first_name"] ~" - "~ lorem'),
            [
                'lorem' => 'Lorem ipsum dolor sit amet consecutir',
            ]
        );

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
                    'firstName' => 'John - Lorem ipsum dolor sit amet consecutir',
                ]
            ]
            );
    }

    public function it_does_keep_preexisting_with_additional_variables_data_as_compiled()
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new Expression('input["employee"]["first_name"] ~" - "~ lorem'),
            [
                'lorem' => 'Lorem ipsum dolor sit amet consecutir',
            ]
        );

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
                    'firstName' => 'John - Lorem ipsum dolor sit amet consecutir',
                ],
            ]
            );
    }
}
