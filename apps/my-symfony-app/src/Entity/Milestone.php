<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MilestoneRepository")
 */
class Milestone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Goal", inversedBy="milestones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $goalBelongsTo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGoalBelongsTo(): ?Goal
    {
        return $this->goalBelongsTo;
    }

    public function setGoalBelongsTo(?Goal $goalBelongsTo): self
    {
        $this->goalBelongsTo = $goalBelongsTo;

        return $this;
    }
}
