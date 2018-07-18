<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    /**
     * @Route("/modifier/{id}", name="utilisateur_modifier")
     */
    public function update(Request $requete, $id, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)->find($id);

        $lastFileName = $user->getAvatar();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {
                $user->setAvatar($lastFileName);
            }
            else {
                if (file_exists($this->getParameter('avatar_directory').'/'.$lastFileName)) {
                    unlink($this->getParameter('avatar_directory').'/'.$lastFileName);
                }
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $user->setAvatar($fileName);

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );
            }
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $user->setModifierA(new \DateTime());

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
     * @Route("/supprimer/{id}", name="utilisateur_supprimer")
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)->find($id);

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('utilisateur_liste');
    }
}
