<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActiviteRepository")
 */
class Activite
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
     * @ORM\Column(type="datetime")
     */
    private $creer_a;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modifier_a;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Planning", mappedBy="activite")
     */
    private $plannings;

    public function __construct()
    {
        $this->plannings = new ArrayCollection();
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

    /**
     * @return Collection|Planning[]
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): self
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings[] = $planning;
            $planning->setActivite($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): self
    {
        if ($this->plannings->contains($planning)) {
            $this->plannings->removeElement($planning);
            // set the owning side to null (unless already changed)
            if ($planning->getActivite() === $this) {
                $planning->setActivite(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->titre;
    }
}
