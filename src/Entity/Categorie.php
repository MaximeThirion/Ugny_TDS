<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategorieRepository")
 */
class Categorie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Article", mappedBy="categories")
     */
    private $articles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    private $file;

    public function __construct()
    {
        $this->categorie_idcategorie = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getCategorieIdcategorie(): Collection
    {
        return $this->categorie_idcategorie;
    }

    public function addCategorieIdcategorie(Article $categorieIdcategorie): self
    {
        if (!$this->categorie_idcategorie->contains($categorieIdcategorie)) {
            $this->categorie_idcategorie[] = $categorieIdcategorie;
        }

        return $this;
    }

    public function removeCategorieIdcategorie(Article $categorieIdcategorie): self
    {
        if ($this->categorie_idcategorie->contains($categorieIdcategorie)) {
            $this->categorie_idcategorie->removeElement($categorieIdcategorie);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->label;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->image;
    }

    public function setFile(string $file): self
    {
        $this->image = $file;

        return $this;
    }
}
