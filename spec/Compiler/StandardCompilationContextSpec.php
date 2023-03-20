<?php

declare(strict_types=1);

namespace spec\Kiboko\Component\FastMap\Compiler;

use Kiboko\Component\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use Kiboko\Contract\Mapping\Compiler\CompilationContextInterface;
use Kiboko\Contract\Metadata\ClassMetadataInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class StandardCompilationContextSpec extends ObjectBehavior
{
    public function it_is_initializable(
        PropertyPathInterface $propertyPath
    ): void {
        $this->beConstructedWith($propertyPath);

        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(null);
        $this->getNamespace()->shouldReturn(null);
        $this->getClassName()->shouldReturn(null);
        $this->getClass()->shouldReturn(null);
    }

    public function it_is_containing_context(
        PropertyPathInterface $propertyPath
    ): void {
        $this->beConstructedWith($propertyPath, __DIR__.'/Baz.php', new ClassReferenceMetadata('Baz', 'Foo\\Bar'));
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(__DIR__.'/Baz.php');
        $this->getNamespace()->shouldReturn('Foo\\Bar');
        $this->getClassName()->shouldReturn('Baz');
        $this->getClass()->shouldReturnAnInstanceOf(ClassMetadataInterface::class);
    }

    public function it_is_extracting_context(
        PropertyPathInterface $propertyPath
    ): void {
        $this->beConstructedThrough('build', [$propertyPath, __DIR__, 'Foo\\Bar\\Baz']);
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(__DIR__.'/Baz.php');
        $this->getNamespace()->shouldReturn('Foo\\Bar');
        $this->getClassName()->shouldReturn('Baz');
        $this->getClass()->shouldReturnAnInstanceOf(ClassMetadataInterface::class);
    }

    public function it_is_extracting_context_from_root_class(
        PropertyPathInterface $propertyPath
    ): void {
        $this->beConstructedThrough('build', [$propertyPath, __DIR__, 'Baz']);
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(__DIR__.'/Baz.php');
        $this->getNamespace()->shouldReturn(null);
        $this->getClassName()->shouldReturn('Baz');
        $this->getClass()->shouldReturnAnInstanceOf(ClassMetadataInterface::class);
    }
}
