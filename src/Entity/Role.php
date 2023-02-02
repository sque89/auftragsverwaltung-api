<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private $id;

    #[ORM\Column(type: "string", length: 32, unique: true)]
    private $name;

    #[ORM\Column(type: "string")]
    private $description;

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->name;
    }
}
