<?php
// src/Entity/Schedule.php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $openTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $closeTime = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $availableDays = null;

    public function __construct()
    {
        // Ensure availableDays is initialized as an empty array if null
        $this->availableDays = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;
        return $this;
    }

    public function getOpenTime(): ?\DateTimeInterface
    {
        return $this->openTime;
    }

    public function setOpenTime(\DateTimeInterface $openTime): static
    {
        $this->openTime = $openTime;
        return $this;
    }

    public function getCloseTime(): ?\DateTimeInterface
    {
        return $this->closeTime;
    }

    public function setCloseTime(\DateTimeInterface $closeTime): static
    {
        $this->closeTime = $closeTime;
        return $this;
    }

    public function getAvailableDays(): array
    {
        return $this->availableDays ?? [];
    }

    public function setAvailableDays(?array $availableDays): static
    {
        $this->availableDays = $availableDays;
        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function ensureAvailableDaysInitialized(): void
    {
        if ($this->availableDays === null) {
            $this->availableDays = [];
        }
    }
}
