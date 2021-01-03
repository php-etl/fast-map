<?php

use Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface;
use Kiboko\Component\ETL\FastMap\Compiler;
use PhpParser\PrettyPrinter;

require __DIR__ . '/../vendor/autoload.php';

/** @var \performance\Kiboko\Component\ETL\FastMap\PerformanceTesting[] $testCases */
$testCases = [
    new \performance\Kiboko\Component\ETL\FastMap\ArrayMapping(),
];

$compilerStrategy = new Compiler\Strategy\Spaghetti();

foreach ($testCases as $case) {
    $caseName = substr(get_class($case), strrpos(get_class($case), '\\') + 1);

    $mapper = $case->build();

    $tree = $compilerStrategy->buildTree(
        new \Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath(),
        sprintf('Kiboko\\__Performance__\\Mapper\\%sMapper', $caseName),
        $mapper
    );

    $prettyPrinter = new PrettyPrinter\Standard();
    file_put_contents(sprintf(__DIR__ . '/compiled/%s.php', $caseName), $prettyPrinter->prettyPrintFile($tree));
    include sprintf(__DIR__ . '/compiled/%s.php', $caseName);
    $fqcn = sprintf('Kiboko\\__Performance__\\Mapper\\%sMapper', $caseName);

    $mapper = new $fqcn();

    $report = [];
    foreach ([1, 1, 3, 10, 30, 100, 300, 1000, 3000, 10000, 30000, 100000] as $size) {
        $times = [];
        for ($i = 0; $i < 10; ++$i) {
            $data = $case->data($size);

            $times[] = test($mapper, $data);
        }

        $report[] = [
            'size' => $size,
            'times' => $times
        ];
    }
}

function test(CompiledMapperInterface $mapper, iterable $products): float
{
    $start = microtime(true);
    foreach ($products as $product) {
        $mapper($product);
    }
    $stop = microtime(true);

    return $stop - $start;
}
function formatMicroTime(float $microtime): string
{
    return sprintf('%3.18f', $microtime);
}

echo "  \033[31mItem Count\033[0m |                 \033[35mMean\033[0m |                  \033[35mMin\033[0m |                  \033[35mMax\033[0m" . PHP_EOL;
echo "-------------|----------------------|----------------------|----------------------" . PHP_EOL;

foreach ($report as $line) {
    printf(
        "      \033[31m%6s\033[0m | \033[35m%3.18f\033[0m | \033[35m%3.18f\033[0m | \033[35m%3.18f\033[0m" . PHP_EOL,
        $line['size'],
        formatMicroTime(array_sum($line['times']) / count($line['times'])),
        formatMicroTime(min(...$line['times'])),
        formatMicroTime(max(...$line['times'])),
    );
}

echo "-------------|----------------------|----------------------|----------------------" . PHP_EOL;
