<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UtilisateurController
 * @package App\Controller
 * @Route("/admin")
 */

class UtilisateurController extends Controller
{
    /**
     * @Route("/utilisateur/modifier/{id}", name="utilisateur_modifier")
     */
    public function utilisateur_modifier(Request $request, $id, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

        $lastFileName = $utilisateur->getAvatar();

        $form = $this->createForm(UtilisateurType::class, $utilisateur);

        $form->handleRequest($request);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {

                $utilisateur->setAvatar($lastFileName);
            }
            else {

                if (file_exists($this->getParameter('avatar_directory').'/'.$lastFileName)) {
                    unlink($this->getParameter('avatar_directory').'/'.$lastFileName);
                    unlink($this->getParameter('avatar_directory_public').'/'.$lastFileName);
                }
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $utilisateur->setAvatar($fileName);

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );
            }
            $password = $passwordEncoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($password);

            $utilisateur->setModifierA(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('utilisateur_liste');
        }
        return $this->render('utilisateur/utilisateur_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/utilisateur/supprimer/{id}", name="utilisateur_supprimer")
     */
    public function utilisateur_supprimer($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

        $lastFileName = $utilisateur->getAvatar();

        if (file_exists($this->getParameter('avatar_directory').'/'.$lastFileName)) {
            unlink($this->getParameter('avatar_directory').'/'.$lastFileName);
            unlink($this->getParameter('avatar_directory_public').'/'.$lastFileName);
        }

        $entityManager->remove($utilisateur);
        $entityManager->flush();

        return $this->redirectToRoute('utilisateur_liste');
    }

    /**
     * @Route("/utilisateur/liste", name="utilisateur_liste")
     */
    public function utilisateur_liste()
    {
        $listeUtilisateur = $this
            ->getDoctrine()
            ->getRepository(Utilisateur::class)
            ->findAll();

        return $this->render('utilisateur/utilisateur_liste.html.twig', [
            'title' => 'Liste',
            'listeUtilisateur' => $listeUtilisateur,
        ]);
    }
}
