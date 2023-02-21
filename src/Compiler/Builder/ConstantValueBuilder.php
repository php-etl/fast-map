<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;

final readonly class ConstantValueBuilder implements Builder
{
    public function __construct(private mixed $value)
    {
    }

    public function getNode(): Node
    {
        return $this->asNode($this->value);
    }

    private function asNode($value)
    {
        if (\is_string($this->value)) {
            return new Node\Scalar\String_($value);
        }
        if (\is_int($this->value)) {
            return new Node\Scalar\LNumber($value);
        }
        if (\is_float($this->value)) {
            return new Node\Scalar\DNumber($value);
        }
        if (\is_array($this->value)) {
            return new Node\Expr\Array_(iterator_to_array($this->asArrayItemNodes($value)));
        }

        throw new \RuntimeException(strtr('Could not handle static value of type %type%, only string, double, integer and array are supported.', ['%type%' => get_debug_type($value)]));
    }

    private function asArrayItemNodes(array $value): \Iterator
    {
        foreach ($value as $key => $item) {
            yield new Node\Expr\ArrayItem(
                $this->asNode($item),
                $this->asNode($key)
            );
        }
    }
}
