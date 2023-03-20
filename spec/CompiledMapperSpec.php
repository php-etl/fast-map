<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap;

use Kiboko\Component\FastMap\CompiledMapper;
use Kiboko\Component\FastMap\Compiler\Compiler;
use Kiboko\Component\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Contract\Mapping\MapperInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class CompiledMapperSpec extends ObjectBehavior
{
    public function it_is_initializable(
        Compiler $compiler,
        MapperInterface $mapper
    ): void {
        $context = StandardCompilationContext::build(new EmptyPropertyPath(), __DIR__, 'Lorem\\Ipsum');

        $this->beConstructedWith($compiler, $context, $mapper);
        $this->shouldHaveType(CompiledMapper::class);
    }

    public function it_is_compilable(
        PropertyPathInterface $propertyPath,
        Compiler $compiler,
        MapperInterface $mapper
    ): void {
        $context = StandardCompilationContext::build(new EmptyPropertyPath(), __DIR__, 'Lorem\\Ipsum');

        $this->beConstructedWith($compiler, $context, $mapper);

        $compiler->compile($context, $mapper)
            ->shouldBeCalledOnce()
            ->willReturn(new class() implements MapperInterface {
                public function __invoke($input, $output, PropertyPathInterface $propertyPath)
                {
                    return array_merge($output, $input);
                }
            })
        ;

        $this->callOnWrappedObject('__invoke', [
            [
                'first_name' => 'John',
            ],
            [
                'last_name' => 'Doe',
            ],
            new EmptyPropertyPath(),
        ])->shouldReturn([
            'last_name' => 'Doe',
            'first_name' => 'John',
        ]);
    }
}
