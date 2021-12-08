<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Catalogue::class, mappedBy="category", orphanRemoval=true)
     */
    private $catalogues;

    public function __construct()
    {
        $this->catalogues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Catalogue[]
     */
    public function getCatalogues(): Collection
    {
        return $this->catalogues;
    }

    public function addCatalogue(Catalogue $catalogue): self
    {
        if (!$this->catalogues->contains($catalogue)) {
            $this->catalogues[] = $catalogue;
            $catalogue->setCategory($this);
        }

        return $this;
    }

    public function removeCatalogue(Catalogue $catalogue): self
    {
        if ($this->catalogues->removeElement($catalogue)) {
            // set the owning side to null (unless already changed)
            if ($catalogue->getCategory() === $this) {
                $catalogue->setCategory(null);
            }
        }

        return $this;
    }
}
