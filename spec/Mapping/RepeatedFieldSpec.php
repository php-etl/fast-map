<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping;

use Kiboko\Component\ETL\FastMap\Contracts;
use Kiboko\Component\ETL\FastMap\Mapping;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class RepeatedFieldSpec extends ObjectBehavior
{
    function it_is_initializable(Contracts\FieldMapperInterface $inner)
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new Expression('"[customers." ~ loop.index ~ ".name]"'),
            new Expression('input["users"]'),
            $inner
        );

        $this->shouldHaveType(Mapping\RepeatedField::class);
        $this->shouldHaveType(Contracts\FieldScopingInterface::class);
        $this->shouldHaveType(Contracts\CompilableInterface::class);
    }
}