<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PlanningType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class CategorieController
 * @package App\Controller
 * @Route("/admin")
 */

class PlanningController extends Controller
{
    /**
     * @Route("/planning/creer", name="planning_creer")
     */
    public function planning_creer(Request $request)
    {
        $planning = new Planning();

        $form = $this->createForm(PlanningType::class, $planning);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $planning = $form->getData();

            $planning->setCreerA(new \DateTime());
            $planning->setModifierA(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($planning);

            $entityManager->flush();

            return $this->redirectToRoute('planning_liste');
        }

        return $this->render('planning/planning_creer.html.twig', [
            'title' => 'Planning',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/planning/liste", name="planning_liste")
     */
    public function categorie_liste()
    {
        $listePlanning = $this
            ->getDoctrine()
            ->getRepository(Planning::class)
            ->findAll();

        return $this->render('planning/planning_liste.html.twig', [
            'title' => 'Liste',
            'listePlanning' => $listePlanning,
        ]);
    }
}
