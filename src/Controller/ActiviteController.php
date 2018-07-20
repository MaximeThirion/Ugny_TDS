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
    public function activite_creer(Request $request)
    {
        $activite = new Activite();

        $form = $this->createForm(ActiviteType::class, $activite);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $activite = $form->getData();

            $file = $form->get('file')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('activite_directory'),
                $fileName
            );
            $activite->setImage($fileName);

            $activite->setCreerA(new \DateTime());
            $activite->setModifierA(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activite);

            $entityManager->flush();

            return $this->redirectToRoute('activite_liste');
        }

        return $this->render('activite/activite_creer.html.twig', [
            'title' => 'Activite',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activite/liste", name="activite_liste")
     */
    public function activite_liste()
    {
        $listeActivite = $this
            ->getDoctrine()
            ->getRepository(Activite::class)
            ->findAll();

        return $this->render('activite/activite_liste.html.twig', [
            'title' => 'Liste',
            'listeActivite' => $listeActivite,
        ]);
    }
}
