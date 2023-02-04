<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "integer")]
    #[Groups(['api'])]
    private $id;

    #[ORM\Column(type: "integer")]
    #[Groups(['api'])]
    private $workingTime;

    #[ORM\Column(type: "text")]
    #[Groups(['api'])]
    private $description;

    #[ORM\Column(type: "date")]
    #[Groups(['api'])]
    private $date;

    #[ManyToOne(targetEntity: "User")]
    #[JoinColumn(nullable: false)]
    #[Groups(['api'])]
    private $arranger;

    #[ManyToOne(targetEntity: "Job")]
    #[JoinColumn(nullable: false)]
    #[Groups(['api'])]
    private $job;

    #[ORM\Column(type: "datetime_immutable")]
    #[Groups(['api'])]
    private $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    #[Groups(['api'])]
    private $updatedAt;

    #[ORM\Version]
    #[ORM\Column(type: "integer")]
    #[Groups(['api'])]
    private $version;

    public function getId() {
        return $this->id;
    }

    public function getWorkingTime(): ?int {
        return $this->workingTime;
    }

    public function setWorkingTime(int $workingTime): self {
        $this->workingTime = $workingTime;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): self {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime {
        return $this->date;
    }

    public function setDate(\DateTime $date): self {
        $this->date = $date;

        return $this;
    }

    public function getJob(): ?Job {
        return $this->job;
    }

    public function setJob(Job $job): self {
        $this->job = $job;
        return $this;
    }

    public function getArranger(): ?User {
        return $this->arranger;
    }

    public function setArranger(User $arranger): self {
        $this->arranger = $arranger;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable {
        return $this->updatedAt;
    }

    public function setVersion(int $version): self {
        $this->version = $version;
        return $this;
    }

    public function getVersion(): int {
        return $this->version;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps() {
        $this->setUpdatedAt(new \DateTimeImmutable('now'));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTimeImmutable('now'));
        }
    }
}
