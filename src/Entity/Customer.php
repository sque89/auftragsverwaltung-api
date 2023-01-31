<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[Groups(['api'])]
    private $id;

    #[ORM\Column(type: "string", length: 256)]
    #[Groups(['api'])]
    private $name;

    #[ORM\Column(type: "string", length: 7, nullable: true)]
    #[Groups(['api'])]
    private $postcode;

    #[ORM\Column(type: "string", length: 128, nullable: true)]
    #[Groups(['api'])]
    private $city;

    #[ORM\Column(type: "string", length: 128, nullable: true)]
    #[Groups(['api'])]
    private $address;

    #[ORM\Column(type: "string", length: 128, nullable: true)]
    #[Groups(['api'])]
    private $contactPerson;

    #[ORM\Column(type: "string", length: 64, nullable: true)]
    #[Groups(['api'])]
    private $mail;

    #[ORM\Column(type: "string", length: 32, nullable: true)]
    #[Groups(['api'])]
    private $phone;

    #[ORM\Column(type: "string", length: 32, nullable: true)]
    #[Groups(['api'])]
    private $fax;

    public function getId() {
        return $this->id;
    }

    public function setId(int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getPostcode(): ?string {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function setCity(?string $city): self {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string {
        return $this->address;
    }

    public function setAddress(?string $address): self {
        $this->address = $address;

        return $this;
    }

    public function getContactPerson(): ?string {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): self {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function getMail(): ?string {
        return $this->mail;
    }

    public function setMail(?string $mail): self {
        $this->mail = $mail;

        return $this;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): self {
        $this->phone = $phone;

        return $this;
    }

    public function getFax(): ?string {
        return $this->fax;
    }

    public function setFax(?string $fax): self {
        $this->fax = $fax;

        return $this;
    }
}
