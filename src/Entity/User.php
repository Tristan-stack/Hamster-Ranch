<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    private ?int $gold = null;

    /**
     * @var Collection<int, Hamster>
     */
    #[ORM\OneToMany(targetEntity: Hamster::class, mappedBy: 'owner', cascade: ['persist', 'remove'])]
    #[Groups(['read'])]
    private Collection $hamsters;

    public function __construct()
    {
        $this->hamsters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getGold(): ?int
    {
        return $this->gold;
    }

    public function setGold(?int $gold): static
    {
        $this->gold = $gold;

        return $this;
    }

    /**
     * @return Collection<int, Hamster>
     */
    public function getHamsters(): Collection
    {
        return $this->hamsters;
    }

    public function addHamster(Hamster $hamster): static
    {
        if (!$this->hamsters->contains($hamster)) {
            $this->hamsters->add($hamster);
            $hamster->setOwner($this);
        }

        return $this;
    }

    public function removeHamster(Hamster $hamster): static
    {
        if ($this->hamsters->removeElement($hamster)) {
            // set the owning side to null (unless already changed)
            if ($hamster->getOwner() === $this) {
                $hamster->setOwner(null);
            }
        }

        return $this;
    }
}
