<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/admin")
 */

class UserController extends Controller
{
    /**
     * @Route("/utilisateur/liste", name="utilisateur_liste")
     */
    public function utilisateur_liste()
    {
        $listeUtilisateur = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('utilisateur/utilisateur_liste.html.twig', [
            'title' => 'Liste',
            'listeUtilisateur' => $listeUtilisateur,
        ]);
    }
}
