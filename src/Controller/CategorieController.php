<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\CategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategorieController extends Controller
{
    /**
     * @Route("/categorie/{id}", name="categorie_trier")
     */
    public function categorie_trier($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $categorie = $entityManager->getRepository(Categorie::class)->find($id);

        $categorieArticles = $entityManager->getRepository(Categorie::class)->categorie_articles($categorie);

        return $this->render('categorie/categorie_trier.html.twig', [
            'title' => 'Liste',
            'categorie' => $categorie,
            'categorieArticles' => $categorieArticles,
        ]);
    }

    /**
     * @Route("/admin/categorie/creer", name="categorie_creer")
     */

    // Permet la création de différentes catégories
    public function categorie_creer(Request $request)
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        // Si le formulaire est soumit et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Alors,
            // Récupération les informations du formulaire
            $categorie = $form->getData();

            // Récupération du chemin
            $file = $form->get('file')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('categorie_directory'),
                $fileName
            );

            $categorie->setImage($fileName);

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
     * @Route("/admin/categorie/modifier/{id}", name="categorie_modifier")
     */
    public function categorie_modifier(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $categorie = $entityManager->getRepository(Categorie::class)->find($id);

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('categorie_liste');
        }
        return $this->render('categorie/categorie_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/categorie/supprimer/{id}", name="categorie_supprimer")
     */
    public function categorie_supprimer($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $categorie = $entityManager->getRepository(Categorie::class)->find($id);

        $entityManager->remove($categorie);
        $entityManager->flush();

        return $this->redirectToRoute('categorie_liste');
    }

    /**
     * @Route("/admin/categorie/liste", name="categorie_liste")
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
