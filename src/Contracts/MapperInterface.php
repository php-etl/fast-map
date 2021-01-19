<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\Contracts;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface MapperInterface
{
    public function __invoke($input, $output, PropertyPathInterface $outputPath);
}
