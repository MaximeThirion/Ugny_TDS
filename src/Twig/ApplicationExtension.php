<?php

namespace App\Twig;

use App\Entity\Activite;
use App\Entity\Information;
use App\Entity\Partenaire;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ApplicationExtension extends AbstractExtension
{

    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getMessage($entityName) {

        $entitiesMapping = [
            'information' => Information::class,
        ];

        $entity = $entitiesMapping[$entityName];

        $information = $this->doctrine->getRepository($entity)->trouver_information();

        return $information;
    }

    public function getActivite($entityName) {

        $entitiesMapping = [
            'activite' => Activite::class,
        ];

        $entity = $entitiesMapping[$entityName];

        $activite = $this->doctrine->getRepository($entity)->findAll();

        return $activite;
    }

    public function getPartenaire($entityName) {

        $entitiesMapping = [
            'partenaire' => Partenaire::class,
        ];

        $entity = $entitiesMapping[$entityName];

        $partenaire = $this->doctrine->getRepository($entity)->findAll();

        return $partenaire;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getMessage', [$this, 'getMessage']),
            new TwigFunction('getActivite', [$this, 'getActivite']),
            new TwigFunction('getPartenaire', [$this, 'getPartenaire'])
        );
    }
}