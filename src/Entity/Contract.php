<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use App\Entity\ContractType;
use App\Entity\DurationType;
use App\Entity\IdentificationType;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    public function __construct()
    {
    }

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $code = null;

    #[ORM\ManyToOne(targetEntity: ContractType::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContractType $type = null;

    #[ORM\Column(type: 'string', length: 1024)]
    private ?string $subjectEs = null;

    #[ORM\Column(type: 'string', length: 1024)]
    private ?string $subjectEu = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amountWithVAT = null;

    #[ORM\ManyToOne(targetEntity: DurationType::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DurationType $durationType = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $duration = null;

    #[ORM\ManyToOne(targetEntity: IdentificationType::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?IdentificationType $identificationType = null;

    #[ORM\Column(type: 'string', length: 15)]
    private ?string $idNumber = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string|bool|null $enterprise = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $awardDate = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contracts')]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $notified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $responseId = null;

    #[ORM\Column(type: 'string', length: 10000, nullable: true)]
    private ?string $rawResponse = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $amountWithoutVAT = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return mb_strtoupper((string) $this->code);
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

    public function getNotified(): ?bool
    {
        return $this->notified;
    }

    public function setNotified(?bool $notified): self
    {
        $this->notified = $notified;

        return $this;
    }

    public function getResponseId(): ?string
    {
        return $this->responseId;
    }

    public function setResponseId(?string $responseId): self
    {
        $this->responseId = $responseId;

        return $this;
    }

    public function getAmountWithoutVAT(): ?string
    {
        return $this->amountWithoutVAT;
    }

    public function setAmountWithoutVAT(?string $amountWithoutVAT): self
    {
        $this->amountWithoutVAT = $amountWithoutVAT;

        return $this;
    }

    public function getRawResponse(): ?string
    {
        return $this->rawResponse;
    }

    public function setRawResponse($rawResponse): self
    {
        $this->rawResponse = $rawResponse;

        return $this;
    }
}
