<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\Mapping\Composite;

use functional\Kiboko\Component\ETL\FastMap as test;
use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Component\ETL\FastMap\Mapping\Composite\ObjectMapper;
use Kiboko\Component\ETL\FastMap\SimpleObjectInitializer;
use Kiboko\Component\ETL\Metadata\ClassReferenceMetadata;
use PhpParser\Node\Expr\Variable;
use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ObjectMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            new SimpleObjectInitializer(
                new ClassReferenceMetadata('Customer'),
                new ExpressionLanguage()
            )
        );
        $this->shouldHaveType(ObjectMapper::class);
    }

    function it_is_mapping_data()
    {
        $interpreter = new ExpressionLanguage();
        $this->beConstructedWith(
            new SimpleObjectInitializer(
                new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\ETL\FastMap\DTO'),
                $interpreter,
                new Expression('input["employee"]["first_name"]'),
                new Expression('input["employee"]["last_name"]')
            )
        );

        $this->callOnWrappedObject(
            '__invoke', [
                [
                    'employee' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ]
                ],
                [],
                new EmptyPropertyPath()
            ])
            ->shouldreturn(new test\DTO\Customer('John', 'Doe'));
    }

    function it_is_mapping_data_while_compiled()
    {
        $interpreter = new ExpressionLanguage();

        $this->beConstructedWith(
            new SimpleObjectInitializer(
                new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\ETL\FastMap\DTO'),
                $interpreter,
                new Expression('input["employee"]["first_name"]'),
                new Expression('input["employee"]["last_name"]')
            )
        );

        $this->compile(new Variable('output'))
            ->shouldExecuteCompiledMapping(
                [
                    'employee' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ]
                ],
                null,
                new test\DTO\Customer('John', 'Doe')
            );
    }
}