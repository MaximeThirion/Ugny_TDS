<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PlanningType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PlanningController extends Controller
{
    /**
     * @Route("/", name="planning")
     */
    public function planning()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $planning = $entityManager->getRepository(Planning::class)->planning_accueil();

        return $this->render('accueil/planning_accueil.html.twig', [
            'title' => 'Accueil',
            'planning' => $planning,
        ]);
    }

    /**
     * @Route("/participe/{id}", name="participe")
     */
    public function participe($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $utilisateur = $this->getUser();

        $planning = $entityManager->getRepository(Planning::class)->find($id);

        $utilisateur->addPlanning($planning);
        $planning->addUtilisateur($utilisateur);

        $this->addFlash(
            'notification',
            'Vous particitez desormais à l\'activité '.$planning->getActivite()->getTitre().' du '.$planning->getDate()->format('d-m-Y à H:i')
        );

        $entityManager->flush();

        return $this->redirectToRoute('planning');
    }

    /**
     * @Route("/participepas/{id}", name="participe_pas")
     */
    public function participe_pas($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $utilisateur = $this->getUser();

        $planning = $entityManager->getRepository(Planning::class)->find($id);

        $utilisateur->removePlanning($planning);
        $planning->removeUtilisateur($utilisateur);

        $this->addFlash(
            'notification',
            'Vous ne particitez plus à l\'activité '.$planning->getActivite()->getTitre().' du '.$planning->getDate()->format('d-m-Y à H:i')
        );

        $entityManager->flush();

        return $this->redirectToRoute('planning');
    }

    /**
     * @Route("/admin/planning/creer", name="planning_creer")
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
     * @Route("/admin/planning/modifier/{id}", name="planning_modifier")
     */
    public function planning_modifier(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $planning = $entityManager->getRepository(Planning::class)->find($id);

        $form = $this->createForm(PlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $planning->setModifierA(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('planning_liste');
        }
        return $this->render('planning/planning_modifier.html.twig', [
            'title' => 'Planning',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/planning/supprimer/{id}", name="planning_supprimer")
     */
    public function planning_supprimer($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $planning = $entityManager->getRepository(Planning::class)->find($id);

        $planning->getActivite()->removePlanning($planning);

        $entityManager->remove($planning);
        $entityManager->flush();

        return $this->redirectToRoute('planning_liste');
    }

    /**
     * @Route("/admin/planning/liste", name="planning_liste")
     */
    public function planning_liste()
    {
        $listePlanning = $this
            ->getDoctrine()
            ->getRepository(Planning::class)
            ->findBy(array(), array('date' => 'ASC'));

        return $this->render('planning/planning_liste.html.twig', [
            'title' => 'Liste',
            'listePlanning' => $listePlanning,
        ]);
    }
}
