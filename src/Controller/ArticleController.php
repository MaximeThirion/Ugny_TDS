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
            $article->setModifierA(new \DateTime());

            $article->setLienVideo('https://www.youtube.com/embed/'.$form->get('lien_video')->getData());

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
     * @Route("/article/modifier/{id}", name="article_modifier")
     */
    public function article_modifier(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $lastFileName = $article->getImage();

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {

                $article->setImage($lastFileName);
            }
            else {

                if (file_exists($this->getParameter('article_directory').'/'.$lastFileName)) {
                    unlink($this->getParameter('article_directory').'/'.$lastFileName);
                    unlink($this->getParameter('article_directory_public').'/'.$lastFileName);
                }

                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $article->setImage($fileName);

                $file->move(
                    $this->getParameter('article_directory'),
                    $fileName
                );
            }
            $article->setModifierA(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('article_liste');
        }
        return $this->render('article/article_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/supprimer/{id}", name="article_supprimer")
     */
    public function article_supprimer($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $lastFileName = $article->getImage();

        if (file_exists($this->getParameter('article_directory').'/'.$lastFileName)) {
            unlink($this->getParameter('article_directory').'/'.$lastFileName);
            unlink($this->getParameter('article_directory_public').'/'.$lastFileName);
        }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('article_liste');
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
