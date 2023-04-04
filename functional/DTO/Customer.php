<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\DTO;

final class Customer
{
    public ?string $email = null;
    /** @var Address[] */
    private array $addresses = [];

    public function __construct(public ?string $firstName = null, public ?string $lastName = null, public ?Address $mainAddress = null)
    {
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setAddresses(Address ...$addresses): void
    {
        $this->addresses = $addresses;
    }

    public function addAddress(Address ...$addresses): void
    {
        $this->addresses = array_merge(
            $this->addresses,
            $addresses
        );
    }

    public function removeAddress(Address ...$addresses): void
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
