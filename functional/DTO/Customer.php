<?php declare(strict_types=1);

namespace functional\Kiboko\Component\FastMap\DTO;

final class Customer
{
    public ?string $firstName;
    public ?string $lastName;
    public ?string $email;
    public ?Address $mainAddress;
    /** @var Address[] */
    private array $addresses;

    public function __construct(?string $firstName = null, ?string $lastName = null, ?Address $mainAddress = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->mainAddress = $mainAddress;
        $this->addresses = [];
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

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
