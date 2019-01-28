<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContactController extends Controller
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, \Swift_Mailer $mailer)
    {

        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = (new \Swift_Message())
                ->setFrom($form->get('email')->getData())
                ->setTo('adminugny@yopmail.com')
                ->setSubject($form->get('sujet')->getData())
                ->setBody(
                    $this->renderView(
                        'emails/contact.html.twig',
                        ['form' => $form->createView()]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('planning');
        }

        return $this->render('contact/contact.html.twig', [
            'title' => 'Contact',
            'form' => $form->createView(),
        ]);
    }
}
