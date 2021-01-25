<?php declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\Mapping\Composite;

use functional\Kiboko\Component\FastMap as test;
use Kiboko\Component\FastMap\Compiler;
use Kiboko\Component\FastMap\Contracts\CompiledMapperInterface;
use Kiboko\Component\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Component\FastMap\Mapping\Composite\ObjectMapper;
use Kiboko\Component\FastMap\SimpleObjectInitializer;
use Kiboko\Component\Metadata\ClassReferenceMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ObjectMapperTest extends TestCase
{
    public function mappingDataProvider()
    {
        $interpreter = new ExpressionLanguage();

        yield [
            new test\DTO\Customer('John', 'Doe'),
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            $interpreter,
            new EmptyPropertyPath(),
            new Expression('input["employee"]["first_name"]'),
            new Expression('input["employee"]["last_name"]'),
        ];

        yield [
            [
                'person' => new test\DTO\Customer('John', 'Doe'),
            ],
            [
                'employee' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ],
            $interpreter,
            new PropertyPath('[person]'),
            new Expression('input["employee"]["first_name"]'),
            new Expression('input["employee"]["last_name"]'),
        ];
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testDynamicResults(
        $expected,
        $input,
        ExpressionLanguage $interpreter,
        PropertyPathInterface $outputField,
        Expression ...$expression
    ) {
        $staticMapper = new ObjectMapper(
            new SimpleObjectInitializer(
                new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'),
                $interpreter,
                ...$expression
            )
        );

        $this->assertEquals($expected, $staticMapper($input, [], $outputField));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResultsWithSpaghettiStrategy(
        $expected,
        $input,
        ExpressionLanguage $interpreter,
        PropertyPathInterface $outputField,
        Expression ...$expression
    ) {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Spaghetti());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new ObjectMapper(
                new SimpleObjectInitializer(
                    new ClassReferenceMetadata('Customer', 'functional\Kiboko\Component\FastMap\DTO'),
                    $interpreter,
                    ...$expression
                )
            )
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}
