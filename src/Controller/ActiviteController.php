<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ArticleController
 * @package App\Controller
 * @Route("/admin")
 */

class ActiviteController extends Controller
{
    /**
     * @Route("/activite/creer", name="activite_creer")
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

        // Je créer le rendu twig (template) de ma fonction créer
        return $this->render('activite/activite_creer.html.twig', [
            'title' => 'Activite',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activite/liste", name="activite_liste")
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
}
