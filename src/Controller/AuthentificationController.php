<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthentificationController
 * @package App\Controller
 * @Route("/authentification")
 */
class AuthentificationController extends Controller
{
    /**
     * @Route("/inscription", name="inscription")
     */
    // Fonction qui permet de créer un utilisateur et d'encoder le mot de passe d'un utilisateur
    public function inscription(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {
        $utilisateur = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $utilisateur = $form->getData();

            $password = $passwordEncoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($password);

            $file = $form->get('file')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('avatar_directory'),
                $fileName
            );
            $utilisateur->setAvatar($fileName);
            $utilisateur->setCreerA(new \DateTime());
            $utilisateur->setModifierA(new \DateTime());
//            $utilisateur->setRoles(['ROLE_ADMIN']);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $code = $this->createCode($utilisateur);

            $activation_link = $this->generateUrl(
                'inscription_activation',
                ['code' => $code, 'id' => $utilisateur->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $message = (new \Swift_Message('Test email'))
                ->setFrom('activation@exemple.com')
                ->setTo($utilisateur->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/inscription.html.twig',
                        ['activation_link' => $activation_link]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('utilisateur_liste');
        }

        return $this->render('authentification/inscription.html.twig', [
            'title' => 'Inscription',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activation/{code}/{id}", name="inscription_activation")
     */
    public function activation($code, $id)
    {
        $utilisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->find($id);

        if ($code === $this->createCode($utilisateur)) {

            $utilisateur->setActif(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($utilisateur);

            $entityManager->flush();

            $this->addFlash(
                'compte',
                'Compte activé');
        } else {

            $this->addFlash(
                'compte',
                'Mauvais code d\'activation');
        }
        return $this->redirectToRoute('planning');
    }

    /**
     * @Route("/connexion", name="connexion")
     */
    public function connexion(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('authentification/connexion.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'title' => 'Connexion',
        ));
    }

    /**
     * @Route("/deconnexion", name="deconnexion")
     */
    public function deconnexion() {
    }

    private function createCode(Utilisateur $utilisateur)
    {
        return sha1('fs5g51sgsv5svss2vb2tn2y1t2ng3f6n' . $utilisateur->getId() . $utilisateur->getEmail());
    }
}
