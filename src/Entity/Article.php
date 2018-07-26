<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
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
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sous_titre;

    /**
     * @ORM\Column(type="text")
     */
    private $contenu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={ "image/png", "image/jpg", "image/jpeg" })
     */
    private $image;

    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lien_video;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creer_a;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modifier_a;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Categorie", inversedBy="articles")
     */
    private $categories;

    /**
     * @ORM\Column(type="boolean")
     */
    private $afficher = false;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId()
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

    public function getSousTitre(): ?string
    {
        return $this->sous_titre;
    }

    public function setSousTitre(string $sous_titre): self
    {
        $this->sous_titre = $sous_titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getLienVideo(): ?string
    {
        return $this->lien_video;
    }

    public function setLienVideo(?string $lien_video): self
    {
        $this->lien_video = $lien_video;

        return $this;
    }

    public function getCreerA(): ?\DateTimeInterface
    {
        return $this->creer_a;
    }

    public function setCreerA(\DateTimeInterface $creer_a): self
    {
        $this->creer_a = $creer_a;

        return $this;
    }

    public function getModifierA(): ?\DateTimeInterface
    {
        return $this->modifier_a;
    }

    public function setModifierA(?\DateTimeInterface $modifier_a): self
    {
        $this->modifier_a = $modifier_a;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->image;
    }

    public function setFile(?string $file): self
    {
        $this->image = $file;

        return $this;
    }

    /**
     * @return Collection|Categorie[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categorie $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addCategorieIdcategorie($this);
        }

        return $this;
    }

    public function removeCategory(Categorie $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removeCategorieIdcategorie($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->titre;
    }

    public function getAfficher(): ?bool
    {
        return $this->afficher;
    }

    public function setAfficher(bool $afficher): self
    {
        $this->afficher = $afficher;

        return $this;
    }
}
