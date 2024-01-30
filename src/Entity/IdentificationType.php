<?php

namespace App\Entity;

use App\Repository\IdentificationTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IdentificationTypeRepository::class)]
class IdentificationType implements \Stringable
{

    final public const IDENTIFICATION_TYPE_CIF=1;
    final public const IDENTIFICATION_TYPE_NIF=2;
    final public const IDENTIFICATION_TYPE_EXTRANJERO=3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Contract::class, mappedBy: 'identificationType')]
    private Collection|array $contracts;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

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
            $contract->setIdentificationType($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->removeElement($contract)) {
            // set the owning side to null (unless already changed)
            if ($contract->getIdentificationType() === $this) {
                $contract->setIdentificationType(null);
            }
        }

        return $this;
    }

    public function __toString(): string {
        return $this->id.'';
    }
}
