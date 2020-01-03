<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap\DTO;

final class Address
{
    public string $name;
    public string $street;
    public string $city;

    public static function build(?string $name = null, ?string $street = null, ?string $city = null)
    {
        $instance = new self;
        $instance->name = $name;
        $instance->street = $street;
        $instance->city = $city;

        return $instance;
    }
}