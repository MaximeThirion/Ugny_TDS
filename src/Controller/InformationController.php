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
}
