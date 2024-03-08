<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('name')]
#[Vich\Uploadable]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 50)]
    private string $name;

    #[Vich\UploadableField(mapping: 'recipe_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true, type: 'string')]
    private ?string $imageName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive()]
    #[Assert\LessThan(200)]
    private ?int $time = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive()]
    #[Assert\LessThan(51)]
    private ?int $nbPeople = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive()]
    #[Assert\LessThan(6)]
    private ?int $difficulty = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    private string $description;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\Positive()]
    #[Assert\LessThan(1001)]
    private ?float $price = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isFavorite;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull()]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull()]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToMany(targetEntity: Ingredient::class)]
    private Collection $ingredients;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type:'boolean')]
    private $isPublic = false;

    #[ORM\OneToMany(targetEntity: Mark::class, mappedBy: 'recipe', orphanRemoval: true)]
    private Collection $marks;

    private ?float $average = null;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->marks = new ArrayCollection();
    }

    /**
     * CONSTRUCTOR 
     */

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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }
    public function getImageName(): ?string
    {
        return $this->imageName;
    }
    

    public function getTime(): ?int
    {
        return $this->time;
    }
    public function setTime(?int $time): static
    {
        $this->time = $time;
        return $this;
    }

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }
    public function setNbPeople(?int $nbPeople): static
    {
        $this->nbPeople = $nbPeople;
        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }
    public function setDifficulty(?int $difficulty): static
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }
    public function setPrice(?float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function isIsFavorite(): ?bool
    {
        return $this->isFavorite;
    }
    public function setIsFavorite(bool $isFavorite): static
    {
        $this->isFavorite = $isFavorite;
        return $this;
    }

    public function isIsPublic(): ?bool
    {
        return $this->isPublic;
    }
    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
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

    #[ORM\PrePersist()]
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
        }
        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        $this->ingredients->removeElement($ingredient);

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

    /**
     * @return Collection<int, Mark>
     */
    public function getMarks(): Collection
    {
        return $this->marks;
    }

    public function addMark(Mark $mark): static
    {
        if (!$this->marks->contains($mark)) {
            $this->marks->add($mark);
            $mark->setRecipe($this);
        }

        return $this;
    }

    public function removeMark(Mark $mark): static
    {
        if ($this->marks->removeElement($mark)) {
            // set the owning side to null (unless already changed)
            if ($mark->getRecipe() === $this) {
                $mark->setRecipe(null);
            }
        }

        return $this;
    }

    public function getAverage()
    {
        $marks = $this->marks;
        if($marks->toArray() === []) {
            $this->average = null;
            return $this->average;
        }
        $total = 0;
        foreach ($marks as $mark){
            $total += $mark->getMark();
        }
        $this->average = $total / count($marks);
        return$this->average;
    }
  
}
