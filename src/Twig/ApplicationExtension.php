<?php

namespace App\Twig;

use App\Entity\Information;
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

    public function getFunctions()
    {
        return array(
            new TwigFunction('getMessage', [$this, 'getMessage'])
        );
    }
}