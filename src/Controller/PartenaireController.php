<?php

namespace App\Controller;

use App\Entity\Partenaire;
use App\Form\PartenaireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PartenaireController extends AbstractController
{
    /**
     * @Route("/partenaire/{id}", name="partenaire_page")
     */
    public function partenaire_page($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $partenaire = $entityManager->getRepository(Partenaire::class)->find($id);

        return $this->render('partenaire/partenaire_page.html.twig', [
            'partenaire' => $partenaire,
        ]);
    }

    /**
     * @Route("/admin/partenaire/creer", name="partenaire_creer")
     */
    public function partenaire_creer(Request $request)
    {
        $partenaire = new Partenaire();

        // J'instancie le formulaire avec ActiviteType (form builder)
        $form = $this->createForm(PartenaireType::class, $partenaire);

        // Je demande au formulaire de considérer la requete
        $form->handleRequest($request);

        // Si le formulaire est soumit et valide, alors
        if ($form->isSubmitted() && $form->isValid()) {

            // J'enregistre les données du formulaire dans la variable $activite
            $partenaire = $form->getData();

            // J'enregistre les données de l'input 'file' du formulaire dans la variable $file
            $file = $form->get('file')->getData();
            // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            // Je déplace le fichier uploadé dans le repertoire 'activite_directory' et je lui donne le nom contenu par $fileName
            $file->move(
                $this->getParameter('partenaire_directory_public'),
                $fileName
            );

            // J'attribue le chemin relatif de l'acitivité contenu dans '$fileName'
            $partenaire->setImage($fileName);

            // J'attribue la date de création et de modification à la date de l'instant
            $partenaire->setCreerA(new \DateTime());
            $partenaire->setModifierA(new \DateTime());

            // J'instancie entityManager
            $entityManager = $this->getDoctrine()->getManager();
            // Je confie '$activite' à Doctrine
            $entityManager->persist($partenaire);

            // J'execute la requète avec doctrine
            $entityManager->flush();

            // Si tout c'est bien passé, je redirige l'utilisateur sur la route 'activite/liste'
            return $this->redirectToRoute('partenaire_liste');
        }
        return $this->render('partenaire/partenaire_creer.html.twig', [
            'title' => 'Partenaire',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/partenaire/modifier/{id}", name="partenaire_modifier")
     */

    // Fonction qui permet de modifier un article
    public function partenaire_modifier(Request $request, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();

        $partenaire = $entityManager->getRepository(Partenaire::class)->find($id);

        $lastFileName = $partenaire->getImage();

        $form = $this->createForm(PartenaireType::class, $partenaire);

        $form->handleRequest($request);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {

                $partenaire->setImage($lastFileName);
            }
            else {

                if (file_exists($this->getParameter('partenaire_directory_public').'/'.$lastFileName)) {

                    unlink($this->getParameter('partenaire_directory_public').'/'.$lastFileName);
                }

                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $partenaire->setImage($fileName);

                $file->move(
                    $this->getParameter('partenaire_directory_public'),
                    $fileName
                );
            }

            $partenaire->setModifierA(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('partenaire_liste');
        }
        return $this->render('partenaire/partenaire_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/partenaire/supprimer/{id}", name="partenaire_supprimer")
     */
    public function partenaire_supprimer($id)
    {

        $entityManager = $this->getDoctrine()->getManager();

        $partenaire = $entityManager->getRepository(Partenaire::class)->find($id);

        $lastFileName = $partenaire->getImage();

        if (file_exists($this->getParameter('partenaire_directory_public').'/'.$lastFileName)) {

            unlink($this->getParameter('partenaire_directory_public').'/'.$lastFileName);
        }

        $entityManager->remove($partenaire);
        $entityManager->flush();

        return $this->redirectToRoute('partenaire_liste');
    }

    /**
     * @Route("/admin/partenaire/liste", name="partenaire_liste")
     */
    public function partenaire_liste()
    {
        // J'enregistre les activités dans '$listeActivite'
        $listePartenaire = $this
            ->getDoctrine()
            ->getRepository(Partenaire::class)
            ->findAll();

        // Je créer le rendu twig (template) de ma fonction 'lister'
        return $this->render('partenaire/partenaire_liste.html.twig', [
            // Je passe mes variables au template twig
            'title' => 'Liste',
            'listePartenaire' => $listePartenaire,
        ]);
    }
}
