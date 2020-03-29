<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GoalRepository")
 */
class Goal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="goals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userBelongsTo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Purpose", inversedBy="goals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purpose;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Habit", mappedBy="goal", orphanRemoval=true)
     */
    private $habits;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $public;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Milestone", mappedBy="goalBelongsTo", cascade={"persist"}, orphanRemoval=true)
     */
    protected $milestones;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="sharedGoals")
     */
    private $usersAssociatedTo;

    public function __construct()
    {
        $this->habits = new ArrayCollection();
        $this->milestones = new ArrayCollection();
        $this->usersAssociatedTo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserBelongsTo(): ?User
    {
        return $this->userBelongsTo;
    }

    public function setUserBelongsTo(?User $userBelongsTo): self
    {
        $this->userBelongsTo = $userBelongsTo;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
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

    public function getPurpose(): ?Purpose
    {
        return $this->purpose;
    }

    public function setPurpose(?Purpose $purpose): self
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * @return Collection|Habit[]
     */
    public function getHabits(): Collection
    {
        return $this->habits;
    }

    public function addHabit(Habit $habit): self
    {
        if (!$this->habits->contains($habit)) {
            $this->habits[] = $habit;
            $habit->setGoal($this);
        }

        return $this;
    }

    public function removeHabit(Habit $habit): self
    {
        if ($this->habits->contains($habit)) {
            $this->habits->removeElement($habit);
            // set the owning side to null (unless already changed)
            if ($habit->getGoal() === $this) {
                $habit->setGoal(null);
            }
        }

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): self
    {
        $this->public = $public;

        return $this;
    }

//    /**
//     * @return Collection|Milestone[]
//     */
    public function getMilestones() //: Collection
    {
        return $this->milestones;
    }

    public function addMilestone(Milestone $milestone): self
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones[] = $milestone;
            $milestone->setGoalBelongsTo($this);
        }

        return $this;
    }

    public function removeMilestone(Milestone $milestone): self
    {
        if ($this->milestones->contains($milestone)) {
            $this->milestones->removeElement($milestone);
            // set the owning side to null (unless already changed)
            if ($milestone->getGoalBelongsTo() === $this) {
                $milestone->setGoalBelongsTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsersAssociatedTo(): Collection
    {
        return $this->usersAssociatedTo;
    }

    public function addUsersAssociatedTo(User $usersAssociatedTo): self
    {
        if (!$this->usersAssociatedTo->contains($usersAssociatedTo)) {
            $this->usersAssociatedTo[] = $usersAssociatedTo;
        }

        return $this;
    }

    public function removeUsersAssociatedTo(User $usersAssociatedTo): self
    {
        if ($this->usersAssociatedTo->contains($usersAssociatedTo)) {
            $this->usersAssociatedTo->removeElement($usersAssociatedTo);
        }

        return $this;
    }
}
