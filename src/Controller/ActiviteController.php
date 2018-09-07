<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ActiviteController extends Controller
{
    /**
     * @Route("/admin/activite/creer", name="activite_creer")
     */

    // Fonction qui permet de créer une activité
    public function activite_creer(Request $request)
    {
        // J'instancie l'acitivité
        $activite = new Activite();

        // J'instancie le formulaire avec ActiviteType (form builder)
        $form = $this->createForm(ActiviteType::class, $activite);

        // Je demande au formulaire de considérer la requete
        $form->handleRequest($request);

        // Si le formulaire est soumit et valide, alors
        if ($form->isSubmitted() && $form->isValid()) {

            // J'enregistre les données du formulaire dans la variable $activite
            $activite = $form->getData();

            // J'enregistre les données de l'input 'file' du formulaire dans la variable $file
            $file = $form->get('file')->getData();
            // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            // Je déplace le fichier uploadé dans le repertoire 'activite_directory' et je lui donne le nom contenu par $fileName
            $file->move(
                $this->getParameter('activite_directory'),
                $fileName
            );

            // J'attribue le chemin relatif de l'acitivité contenu dans '$fileName'
            $activite->setImage($fileName);

            // J'attribue la date de création et de modification à la date de l'instant
            $activite->setCreerA(new \DateTime());
            $activite->setModifierA(new \DateTime());

            // J'instancie entityManager
            $entityManager = $this->getDoctrine()->getManager();
            // Je confie '$activite' à Doctrine
            $entityManager->persist($activite);

            // J'execute la requète avec doctrine
            $entityManager->flush();

            // Si tout c'est bien passé, je redirige l'utilisateur sur la route 'activite/liste'
            return $this->redirectToRoute('activite_liste');
        }

        // J'affiche le rendu twig (template) de ma fonction créer
        return $this->render('activite/activite_creer.html.twig', [
            'title' => 'Activite',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/activite/modifier/{id}", name="activite_modifier")
     */

    // Fonction qui permet de modifier un article
    public function activite_modifier(Request $request, $id)
    {
        // J'instancie entityManager
        $entityManager = $this->getDoctrine()->getManager();

        //
        $activite = $entityManager->getRepository(Activite::class)->find($id);

        $lastFileName = $activite->getImage();

        $form = $this->createForm(ActiviteType::class, $activite);

        $form->handleRequest($request);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {

                $activite->setImage($lastFileName);
            }
            else {

                if (file_exists($this->getParameter('activite_directory').'/'.$lastFileName)) {

                    unlink($this->getParameter('activite_directory').'/'.$lastFileName);
                    unlink($this->getParameter('activite_directory_public').'/'.$lastFileName);
                }

                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $activite->setImage($fileName);

                $file->move(
                    $this->getParameter('activite_directory'),
                    $fileName
                );
            }

            $activite->setModifierA(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('activite_liste');
        }
        return $this->render('activite/activite_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/activite/supprimer/{id}", name="activite_supprimer")
     */
    public function activite_supprimer($id)
    {

        $entityManager = $this->getDoctrine()->getManager();

        $activite = $entityManager->getRepository(Activite::class)->find($id);

        $lastFileName = $activite->getImage();

        if (file_exists($this->getParameter('activite_directory').'/'.$lastFileName)) {

            unlink($this->getParameter('activite_directory').'/'.$lastFileName);
            unlink($this->getParameter('activite_directory_public').'/'.$lastFileName);
        }

        $entityManager->remove($activite);
        $entityManager->flush();

        return $this->redirectToRoute('activite_liste');
    }

    /**
     * @Route("/admin/activite/liste", name="activite_liste")
     */

    // Fonction qui permet de lister les activités
    public function activite_liste()
    {
        // J'enregistre les activités dans '$listeActivite'
        $listeActivite = $this
            ->getDoctrine()
            ->getRepository(Activite::class)
            ->findAll();

        // Je créer le rendu twig (template) de ma fonction 'lister'
        return $this->render('activite/activite_liste.html.twig', [
            // Je passe mes variables au template twig
            'title' => 'Liste',
            'listeActivite' => $listeActivite,
        ]);
    }

    /**
     * @Route("/activite/{id}", name="activite_page")
     */

    // fonction qui permet d'afficher la page d'un article par son id
    public function activite_page($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $activite = $entityManager->getRepository(Activite::class)->find($id);

        // J'affiche la template twig liée à la page d'une activité
        return $this->render('activite/activite_page.html.twig', [
            'activite' => $activite,
        ]);
    }
}