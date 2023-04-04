<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\DTO;

final class Address
{
    public ?string $name = null;
    public ?string $street = null;
    public ?string $city = null;

    public static function build(?string $name = null, ?string $street = null, ?string $city = null)
    {
        $instance = new self();
        $instance->name = $name;
        $instance->street = $street;
        $instance->city = $city;

        return $instance;
    }
}
