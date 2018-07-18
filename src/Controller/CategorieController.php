<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class CategorieController
 * @package App\Controller
 * @Route("/admin")
 */

class CategorieController extends Controller
{
    /**
     * @Route("/categorie/creer", name="categorie_creer")
     */
    public function categorie_creer(Request $request)
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categorie = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorie);

            $entityManager->flush();

            return $this->redirectToRoute('categorie_liste');
        }

        return $this->render('categorie/categorie_creer.html.twig', [
            'title' => 'Categorie',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/categorie/liste", name="categorie_liste")
     */
    public function categorie_liste()
    {
        $listeCategorie = $this
            ->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();

        return $this->render('categorie/categorie_liste.html.twig', [
            'title' => 'Liste',
            'listeCategorie' => $listeCategorie,
        ]);
    }
}