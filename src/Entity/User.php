<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AMREU\UserBundle\Model\User as BaseUser;
use App\Repository\UserRepository;

#[ORM\Table(name: 'user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends BaseUser /* implements AMREUserInterface, PasswordAuthenticatedUserInterface */
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected $username;

    #[ORM\Column(type: 'json')]
    protected $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    protected $password;

    #[ORM\Column(type: 'string', length: 255)]
    protected $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    #[ORM\Column(type: 'boolean', options: ['default' => '1'], nullable: true)]
    protected $activated;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $lastLogin;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $idNumber;

    #[ORM\OneToMany(targetEntity: Contract::class, mappedBy: 'user')]
    private Collection|array $contracts;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username;
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts[] = $contract;
            $contract->setUser($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->removeElement($contract)) {
            // set the owning side to null (unless already changed)
            if ($contract->getUser() === $this) {
                $contract->setUser(null);
            }
        }

        return $this;
    }
}
