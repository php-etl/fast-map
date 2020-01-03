<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Compiler;

use Kiboko\Component\ETL\FastMap\Compiler\CompilationContextInterface;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use Kiboko\Component\ETL\Metadata\ClassMetadataInterface;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class StandardCompilationContextSpec extends ObjectBehavior
{
    function it_is_initializable(
        PropertyPathInterface $propertyPath
    ) {
        $this->beConstructedWith($propertyPath);

        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(null);
        $this->getNamespace()->shouldReturn(null);
        $this->getClassName()->shouldReturn(null);
        $this->getClass()->shouldReturn(null);
    }

    function it_is_containing_context(
        PropertyPathInterface $propertyPath
    ) {
        $this->beConstructedWith($propertyPath, __DIR__ . '/Baz.php', new ClassReferenceMetadata('Baz', 'Foo\\Bar'));
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(__DIR__ . '/Baz.php');
        $this->getNamespace()->shouldReturn('Foo\\Bar');
        $this->getClassName()->shouldReturn('Baz');
        $this->getClass()->shouldReturnAnInstanceOf(ClassMetadataInterface::class);
    }

    function it_is_extracting_context(
        PropertyPathInterface $propertyPath
    ) {
        $this->beConstructedThrough('build', [$propertyPath, __DIR__, 'Foo\\Bar\\Baz']);
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(__DIR__ . '/Baz.php');
        $this->getNamespace()->shouldReturn('Foo\\Bar');
        $this->getClassName()->shouldReturn('Baz');
        $this->getClass()->shouldReturnAnInstanceOf(ClassMetadataInterface::class);
    }

    function it_is_extracting_context_from_root_class(
        PropertyPathInterface $propertyPath
    ) {
        $this->beConstructedThrough('build', [$propertyPath, __DIR__, 'Baz']);
        $this->shouldHaveType(StandardCompilationContext::class);
        $this->shouldHaveType(CompilationContextInterface::class);

        $this->getPropertyPath()->shouldReturn($propertyPath);
        $this->getFilePath()->shouldReturn(__DIR__ . '/Baz.php');
        $this->getNamespace()->shouldReturn(null);
        $this->getClassName()->shouldReturn('Baz');
        $this->getClass()->shouldReturnAnInstanceOf(ClassMetadataInterface::class);
    }
}