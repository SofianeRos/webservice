<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TodoRepository;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: TodoRepository::class)]
#[ApiResource(
    normalizationContext: ["groups" => ['todo:read']],
    denormalizationContext: ["groups" => ['todo:write']]
)]
    
#[ApiFilter(
    BooleanFilter::class,
    properties: ['isCompleted', 'isUrgent']
)]
#[ApiFilter(
    SearchFilter::class,
    properties: ['title' => 'ipartial', 'description' => 'ipartial', 'user.id' => 'exact', 'user.email' => 'exact']
)]

class Todo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['todo:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?bool $isCompleted = null;

    #[ORM\Column]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?bool $isUrgent = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?\DateTime $deadline = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?string $mediaPath = null;

    #[ORM\Column]
    #[Groups(['todo:read','todo:write','user:read'])]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['todo:write'])]
    private ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    #[Groups(['todo:read','todo:write'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    public function isUrgent(): ?bool
    {
        return $this->isUrgent;
    }

    public function setIsUrgent(bool $isUrgent): static
    {
        $this->isUrgent = $isUrgent;

        return $this;
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTime $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getMediaPath(): ?string
    {
        return $this->mediaPath;
    }

    public function setMediaPath(?string $mediaPath): static
    {
        $this->mediaPath = $mediaPath;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
