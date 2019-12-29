<?php declare(strict_types=1);

namespace functional\Kiboko\Component\ETL\FastMap\DTO;

final class Customer
{
    public string $firstName;
    public string $lastName;
    public Address $mainAddress;
    /** @var Address[] */
    private array $addresses;

    public function setAddresses(Address ...$addresses)
    {
        $this->addresses = $addresses;
    }

    public function addAddress(Address ...$addresses)
    {
        $this->addresses = array_merge(
            $this->addresses,
            $addresses
        );
    }

    public function removeAddress(Address ...$addresses)
    {
        $this->addresses = array_diff(
            $this->addresses,
            $addresses
        );
    }

    /** @return Address[] */
    public function getAddresses(): iterable
    {
        return $this->addresses;
    }
}