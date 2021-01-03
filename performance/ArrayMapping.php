<?php declare(strict_types=1);

namespace performance\Kiboko\Component\ETL\FastMap;

use Faker;
use Kiboko\Component\ETL\FastMap\Contracts\CompilableMapperInterface;
use Kiboko\Component\ETL\FastMap\Mapping;
use Symfony\Component\PropertyAccess\PropertyPath;

final class ArrayMapping implements PerformanceTesting
{
    public function build(): CompilableMapperInterface
    {
        return new Mapping\Composite\ArrayMapper(
            new Mapping\Field(
                new PropertyPath('[identifier]'),
                new Mapping\Field\CopyValueMapper(new PropertyPath('[sku]'))
            ),
            new Mapping\Field(
                new PropertyPath('[name]'),
                new Mapping\Field\CopyValueMapper(new PropertyPath('[name]'))
            ),
            new Mapping\Field(
                new PropertyPath('[short_description]'),
                new Mapping\Field\CopyValueMapper(new PropertyPath('[description]'))
            )
        );
    }

    public function data(int $size): iterable
    {
        $faker = Faker\Factory::create();
        $data = new \SplFixedArray($size);

        for ($index = 0; $index < $size; ++$index) {
            $data[$index] = [
                'sku' => $faker->slug,
                'name' => $faker->sentence(6, true),
                'description' => $faker->paragraphs(3, true),
            ];
        }

        return $data;
    }
}