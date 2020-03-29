<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Goal", mappedBy="userBelongsTo", orphanRemoval=true)
     */
    private $goals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purpose", mappedBy="userBelongsTo", orphanRemoval=true)
     */
    private $purposes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Habit", mappedBy="userBelongsTo", orphanRemoval=true)
     */
    private $habits;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Goal", mappedBy="usersAssociatedTo")
     */
    private $sharedGoals;

    public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->purposes = new ArrayCollection();
        $this->habits = new ArrayCollection();
        $this->sharedGoals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Goal[]
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals[] = $goal;
            $goal->setUserBelongsTo($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->contains($goal)) {
            $this->goals->removeElement($goal);
            // set the owning side to null (unless already changed)
            if ($goal->getUserBelongsTo() === $this) {
                $goal->setUserBelongsTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Purpose[]
     */
    public function getPurposes(): Collection
    {
        return $this->purposes;
    }

    public function addPurpose(Purpose $purpose): self
    {
        if (!$this->purposes->contains($purpose)) {
            $this->purposes[] = $purpose;
            $purpose->setUserBelongsTo($this);
        }

        return $this;
    }

    public function removePurpose(Purpose $purpose): self
    {
        if ($this->purposes->contains($purpose)) {
            $this->purposes->removeElement($purpose);
            // set the owning side to null (unless already changed)
            if ($purpose->getUserBelongsTo() === $this) {
                $purpose->setUserBelongsTo(null);
            }
        }

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
            $habit->setUserBelongsTo($this);
        }

        return $this;
    }

    public function removeHabit(Habit $habit): self
    {
        if ($this->habits->contains($habit)) {
            $this->habits->removeElement($habit);
            // set the owning side to null (unless already changed)
            if ($habit->getUserBelongsTo() === $this) {
                $habit->setUserBelongsTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Goal[]
     */
    public function getSharedGoals(): Collection
    {
        return $this->sharedGoals;
    }

    public function addSharedGoal(Goal $sharedGoal): self
    {
        if (!$this->sharedGoals->contains($sharedGoal)) {
            $this->sharedGoals[] = $sharedGoal;
            $sharedGoal->addUsersAssociatedTo($this);
        }

        return $this;
    }

    public function removeSharedGoal(Goal $sharedGoal): self
    {
        if ($this->sharedGoals->contains($sharedGoal)) {
            $this->sharedGoals->removeElement($sharedGoal);
            $sharedGoal->removeUsersAssociatedTo($this);
        }

        return $this;
    }
}
