<?php

namespace App\Entity;

use App\Repository\MarkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MarkRepository::class)]
#[UniqueEntity(
    fields:['user','recipe'],
    errorPath: 'user',
    message: 'Vous avez déjà noté cette recette'
)]
class Mark
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\Column(type:'integer')]
    #[Assert\Positive()]
    #[Assert\LessThan(6)]
    private int $mark;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'marks')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'marks')]
    #[ORM\JoinColumn(nullable: false)]
    private Recipe $recipe;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMark(): ?int
    {
        return $this->mark;
    }

    public function setMark(int $mark): static
    {
        $this->mark = $mark;

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

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
