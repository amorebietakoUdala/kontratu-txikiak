<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ContractRepository::class)
 */
class Contract
{

    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=ContractType::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $subjectEs;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $subjectEu;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $amountWithVAT;

    /**
     * @ORM\ManyToOne(targetEntity=DurationType::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $durationType;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity=IdentificationType::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $identificationType;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $idNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $enterprise;

    /**
     * @ORM\Column(type="date")
     */
    private $awardDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="contracts")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return mb_strtoupper($this->code);
    }

    public function setCode(string $code): self
    {
        $this->code = mb_strtoupper($code);

        return $this;
    }

    public function getType(): ?ContractType
    {
        return $this->type;
    }

    public function setType(?ContractType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSubjectEs(): ?string
    {
        return $this->subjectEs;
    }

    public function setSubjectEs(string $subjectEs): self
    {
        $this->subjectEs = $subjectEs;

        return $this;
    }

    public function getSubjectEu(): ?string
    {
        return $this->subjectEu;
    }

    public function setSubjectEu(string $subjectEu): self
    {
        $this->subjectEu = $subjectEu;

        return $this;
    }

    public function getAmountWithVAT(): ?string
    {
        return $this->amountWithVAT;
    }

    public function setAmountWithVAT(string $amountWithVAT): self
    {
        $this->amountWithVAT = $amountWithVAT;

        return $this;
    }

    public function getDurationType(): ?DurationType
    {
        return $this->durationType;
    }

    public function setDurationType(?DurationType $durationType): self
    {
        $this->durationType = $durationType;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getIdentificationType(): ?IdentificationType
    {
        return $this->identificationType;
    }

    public function setIdentificationType(?IdentificationType $identificationType): self
    {
        $this->identificationType = $identificationType;

        return $this;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function setIdNumber(string $idNumber): self
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    public function getEnterprise(): ?string
    {
        return $this->enterprise;
    }

    public function setEnterprise(string $enterprise): self
    {

        $this->enterprise = mb_ereg_replace('&', ' AND ', $enterprise);

        return $this;
    }

    public function getAwardDate(): ?\DateTimeInterface
    {
        return $this->awardDate;
    }

    public function setAwardDate(\DateTimeInterface $awardDate): self
    {
        $this->awardDate = $awardDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
