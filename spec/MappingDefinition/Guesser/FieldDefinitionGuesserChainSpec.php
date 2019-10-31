<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\FieldDefinitionGuesserChain;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser\FieldDefinitionGuesserInterface;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use PhpSpec\ObjectBehavior;

final class FieldDefinitionGuesserChainSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FieldDefinitionGuesserChain::class);
        $this->shouldHaveType(FieldDefinitionGuesserInterface::class);
    }

    function it_is_calling_inner_guessers(
        FieldDefinitionGuesserInterface $guesser1,
        FieldDefinitionGuesserInterface $guesser2
    ) {
        $metadata = new ClassTypeMetadata(\stdClass::class);

        $guesser1->__invoke($metadata)
            ->shouldBeCalledOnce()
            ->willReturn(new \EmptyIterator())
        ;
        $guesser2->__invoke($metadata)
            ->shouldBeCalledOnce()
            ->willReturn(new \EmptyIterator())
        ;

        $this->beConstructedWith($guesser1, $guesser2);

        $this->__invoke($metadata)
            ->shouldIterateAs(new \EmptyIterator());
    }
}