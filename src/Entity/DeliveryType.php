<?php

namespace App\Entity;

use App\Repository\DeliveryTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeliveryTypeRepository::class)]
class DeliveryType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['api'])]
    private $id;

    #[ORM\Column(type: "string", length: 64, nullable: true)]
    #[Groups(['api'])]
    private $label;

    public function getId()
    {
        return $this->id;
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    public function setLabel(string $label): self {
        $this->label = $label;

        return $this;
    }
}
