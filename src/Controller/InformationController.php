<?php

namespace App\Controller;

use App\Entity\Information;
use App\Form\InformationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InformationController extends Controller
{
    /**
     * @Route("/information/creer", name="information_creer")
     */
    public function information_creer(Request $request)
    {
        $information = new Information();

        $form = $this->createForm(InformationType::class, $information);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $information = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($information);

            $entityManager->flush();

            return $this->redirectToRoute('planning');
        }

        return $this->render('information/information_creer.html.twig', [
            'title' => 'Information',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/information/modifier/{id}", name="information_modifier")
     */
    public function information_modifier(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $information = $entityManager->getRepository(Information::class)->find($id);

        $form = $this->createForm(InformationType::class, $information);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('information_liste');
        }
        return $this->render('information/information_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/information/supprimer/{id}", name="information_supprimer")
     */
    public function information_supprimer($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $information = $entityManager->getRepository(Information::class)->find($id);

        $entityManager->remove($information);
        $entityManager->flush();

        return $this->redirectToRoute('information_liste');
    }

    /**
     * @Route("/information/liste", name="information_liste")
     */
    public function information_liste()
    {
        $listeInformation = $this
            ->getDoctrine()
            ->getRepository(Information::class)
            ->findAll();

        return $this->render('information/information_liste.html.twig', [
            'title' => 'Liste',
            'listeInformation' => $listeInformation,
        ]);
    }
}
