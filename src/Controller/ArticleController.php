<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ArticleController
 * @package App\Controller
 * @Route("/admin")
 */

class ArticleController extends Controller
{
    /**
     * @Route("/article/creer", name="article_creer")
     */
    public function article_creer(Request $request)
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article = $form->getData();

            $file = $form->get('file')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('article_directory'),
                $fileName
            );
            $article->setImage($fileName);

            $article->setCreerA(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);

            $entityManager->flush();

            return $this->redirectToRoute('article_liste');
        }

        return $this->render('article/article_creer.html.twig', [
            'title' => 'Article',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/liste", name="article_liste")
     */
    public function article_liste()
    {
        $listeArticle = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        return $this->render('article/article_liste.html.twig', [
            'title' => 'Liste',
            'listeArticle' => $listeArticle,
        ]);
    }
}
