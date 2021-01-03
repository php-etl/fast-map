<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap\Mapper\Composite;

use functional\Kiboko\Component\ETL\FastMap as test;
use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Component\ETL\FastMap\Mapping\Composite\ObjectMapper;
use Kiboko\Component\ETL\FastMap\SimpleObjectInitializer;
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
                'functional\\Kiboko\\Component\\ETL\\FastMap\\DTO\\Customer',
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
                    'functional\\Kiboko\\Component\\ETL\\FastMap\\DTO\\Customer',
                    $interpreter,
                    ...$expression
                )
            )
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }

    /**
     * @dataProvider mappingDataProvider
     */
    public function testCompilationResultsWithReduceStrategy(
        $expected,
        $input,
        ExpressionLanguage $interpreter,
        PropertyPathInterface $outputField,
        Expression ...$expression
    ) {
        $compiler = new Compiler\Compiler(new Compiler\Strategy\Reduce());

        /** @var CompiledMapperInterface $compiledMapper */
        $compiledMapper = $compiler->compile(
            new Compiler\StandardCompilationContext(
                $outputField,
                null,
                null
            ),
            new ObjectMapper(
                new SimpleObjectInitializer(
                    'functional\\Kiboko\\Component\\ETL\\FastMap\\DTO\\Customer',
                    $interpreter,
                    ...$expression
                )
            )
        );

        $this->assertEquals($expected, $compiledMapper($input, []));
    }
}