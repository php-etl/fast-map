<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Contracts;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface ObjectInitializerInterface
{
    public function __invoke($input, $output, PropertyPathInterface $propertyPath);
}
