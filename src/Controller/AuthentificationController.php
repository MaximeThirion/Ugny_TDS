<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ModificationMotDePasseType;
use App\Form\UtilisateurType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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

    /**
     * @Route("/motdepasseoublie", name="mot_de_passe_oublie")
     */
    public function mot_de_passe_oublie(Request $request, \Swift_Mailer $mailer) {

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();

            $code = $this->createCodeMotDePasse($email);

            $activation_link = $this->generateUrl(
                'modification_mot_de_passe',
                ['code' => $code],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $message = (new \Swift_Message('Test email'))
                ->setFrom('modificationmdp@exemple.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        'emails/mot_de_passe_oublie.html.twig',
                        ['activation_link' => $activation_link]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('planning');
        }

        return $this->render('authentification/mot_de_passe_oublie.html.twig', [
            'title' => 'Mot de passe oublié',
            'form' => $form->createView(),
        ]);
    }

    /**
     *@Route("/modificationmotdepasse/{code}", name="modification_mot_de_passe")
     */
    public function modification_mot_de_passe($code, Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $listeUtilisateur = $this
            ->getDoctrine()
            ->getRepository(Utilisateur::class)
            ->findAll();

        foreach ($listeUtilisateur as $utilisateur) {

            if ($code === $this->createCodeMotDePasse($utilisateur->getEmail())) {

                $lastFileName = $utilisateur->getAvatar();

                $form = $this->createForm(ModificationMotDePasseType::class, $utilisateur);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    if ($form->get('file')->getData() === null) {

                        $utilisateur->setAvatar($lastFileName);
                    }

                    $password = $passwordEncoder->encodePassword($utilisateur, $form->get('password')->getData());
                    $utilisateur->setPassword($password);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($utilisateur);

                    $entityManager->flush();

                    $this->addFlash(
                        'password',
                        'Mot de passe changé');

                    return $this->redirectToRoute('connexion');
                }
            }
        }
        return $this->render('authentification/modification_mot_de_passe.html.twig', [
            'title' => 'Reset',
            'form' => $form->createView(),
        ]);
    }

    private function createCode(Utilisateur $utilisateur)
    {
        return sha1('fs5g51sgsv5svss2vb2tn2y1t2ng3f6n' . $utilisateur->getId() . $utilisateur->getEmail());
    }

    private function createCodeMotDePasse($email)
    {
        return sha1('5svdvdvdvdblj46474ss2vb2tn2y1t2ng3f6n' . $email);
    }
}
