<?php

namespace App\Entity;

use App\Repository\DonationRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonationRepository::class)]
class Donation
{
    public const STATUS = [
        'En cours',
        'Finalisé',
        'Annulé'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 30)]
    private string $status;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'donationsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private User $beneficiary;

    #[ORM\ManyToOne(targetEntity: SeedBatch::class, inversedBy: 'donations')]
    #[ORM\JoinColumn(nullable: false)]
    private SeedBatch $seedBatch;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBeneficiary(): ?User
    {
        return $this->beneficiary;
    }

    public function setBeneficiary(?User $beneficiary): self
    {
        $this->beneficiary = $beneficiary;

        return $this;
    }

    public function getSeedBatch(): ?SeedBatch
    {
        return $this->seedBatch;
    }

    public function setSeedBatch(?SeedBatch $seedBatch): self
    {
        $this->seedBatch = $seedBatch;

        return $this;
    }
}
