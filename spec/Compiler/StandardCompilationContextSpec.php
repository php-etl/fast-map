<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Compiler;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContextInterface;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpSpec\ObjectBehavior;

final class StandardCompilationContextSpec extends ObjectBehavior
{
    function it_is_initializable(Compiler $compiler, MapperInterface $mapper)
    {
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->path()->shouldReturn(null);
        $this->namespace()->shouldReturn(null);
        $this->className()->shouldReturn(null);
    }

    function it_is_containing_context(Compiler $compiler, MapperInterface $mapper)
    {
        $this->beConstructedWith(__DIR__, 'Foo\\Bar', 'Baz');
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->path()->shouldReturn(__DIR__);
        $this->namespace()->shouldReturn('Foo\\Bar');
        $this->className()->shouldReturn('Baz');
    }

    function it_is_extracting_context(Compiler $compiler, MapperInterface $mapper)
    {
        $this->beConstructedThrough('build', [__DIR__, 'Foo\\Bar\\Baz']);
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->path()->shouldReturn(__DIR__);
        $this->namespace()->shouldReturn('Foo\\Bar');
        $this->className()->shouldReturn('Baz');
    }

    function it_is_extracting_context_from_root_class(Compiler $compiler, MapperInterface $mapper)
    {
        $this->beConstructedThrough('build', [__DIR__, 'Baz']);
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->path()->shouldReturn(__DIR__);
        $this->namespace()->shouldReturn(null);
        $this->className()->shouldReturn('Baz');
    }
}