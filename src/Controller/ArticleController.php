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
    // Fonction qui permet de créer un article
    public function article_creer(Request $request)
    {
        // J'instancie un aticle
        $article = new Article();

        // J'instancie le formulaire avec ArticleType (form builder)
        $form = $this->createForm(ArticleType::class, $article);

        // Je demande au formulaire de considérer la requete
        $form->handleRequest($request);

        // Si le formulaire est soumit et valide, alors
        if ($form->isSubmitted() && $form->isValid()) {

            // J'enregistre les données du formulaire dans la variable $article
            $article = $form->getData();

            // J'enregistre les données de l'input 'file' du formulaire dans la variable $file
            $file = $form->get('file')->getData();

            // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            // Je déplace le fichier uploadé dans le repertoire 'article_directory' et je lui donne le nom contenu par $fileName
            $file->move(
                $this->getParameter('article_directory'),
                $fileName
            );

            // J'attribue le chemin relatif de l'acitivité contenu dans '$fileName'
            $article->setImage($fileName);

            // J'attribue la date de création et de modification à la date de l'instant + le lien de la vidéo
            $article->setCreerA(new \DateTime());
            $article->setModifierA(new \DateTime());

            $article->setLienVideo('https://www.youtube.com/embed/'.$form->get('lien_video')->getData());

            // J'instancie entityManager
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);

            // J'execute la requète avec doctrine
            $entityManager->flush();

            return $this->redirectToRoute('article_liste');
        }

        // Si tout c'est bien passé, je redirige l'utilisateur sur la route 'article/liste'
        return $this->render('article/article_creer.html.twig', [
            'title' => 'Article',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/modifier/{id}", name="article_modifier")
     */

    // Fonction qui permet de modifier un article
    public function article_modifier(Request $request, $id)
    {
        // J'instancie le manager (fait les opérations via doctrine)
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);

        // J'enregistre l'ancien nom (au cas il existerait deja)
        $lastFileName = $article->getImage();

        // J'instancie le formulaire avec ArticleType (form builder)
        $form = $this->createForm(ArticleType::class, $article);

        // Je demande au formulaire de considérer la requete
        $form->handleRequest($request);
        // J'enregistre les données de l'input 'file' du formulaire dans la variable $file
        $file = $form->get('file')->getData();

        // Si le formulaire est soumit et valide, alors
        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'input fil est = NULL alors
            if ($form->get('file')->getData() === null) {
                // J'attribue l'image de l'article à l'ancien nom
                $article->setImage($lastFileName);
            }
            // sinon
            else {
                // Si l'ancien nom existe déjà dans le repertoire
                if (file_exists($this->getParameter('article_directory').'/'.$lastFileName)) {
                    // Alors on les supprime
                    unlink($this->getParameter('article_directory').'/'.$lastFileName);
                    unlink($this->getParameter('article_directory_public').'/'.$lastFileName);
                }
                // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $article->setImage($fileName);

                // Je déplace le fichier uploadé dans le repertoire 'activite_directory' et je lui donne le nom contenu par $fileName
                $file->move(
                    $this->getParameter('article_directory'),
                    $fileName
                );
            }

            // Je modifier le champ 'modifier_a'
            $article->setModifierA(new \DateTime());
            // j'execute la requete avec doctrine
            $entityManager->flush();

            // Je redirige vers la route 'article_liste'
            return $this->redirectToRoute('article_liste');
        }
        // Je créer le rendu twig (template) de ma fonction modifier
        return $this->render('article/article_modifier.html.twig', [
            'title' => 'Modifier',
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/supprimer/{id}", name="article_supprimer")
     */

    // Fonction qui permet de supprimer un article
    public function article_supprimer($id)
    {
        // J'instancie entityManager
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);
        // J'enregistre l'ancien nom (au cas il existerait deja)
        $lastFileName = $article->getImage();

        // Si l'ancien nom existe déjà dans le repertoire
        if (file_exists($this->getParameter('article_directory').'/'.$lastFileName)) {
            // alors supprimes du directory
            unlink($this->getParameter('article_directory').'/'.$lastFileName);
            unlink($this->getParameter('article_directory_public').'/'.$lastFileName);
        }
        // Je supprime l'article de la base de donnée
        $entityManager->remove($article);
        // j'execute la requete
        $entityManager->flush();

        // Je redirige sur la route 'article_liste'
        return $this->redirectToRoute('article_liste');
    }

    /**
     * @Route("/article/liste", name="article_liste")
     */

    // Fonction qi permet de lister les articles
    public function article_liste()
    {
        // J'enregistre les articles dans '$listeArticles'
        $listeArticle = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        // Je créer le rendu twig (template) de ma fonction 'lister'
        return $this->render('article/article_liste.html.twig', [
            'title' => 'Liste',
            'listeArticle' => $listeArticle,
        ]);
    }

    /**
     * @Route("/article/afficher/{id}", name="article_afficher")
     */
    public function article_afficher($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $article->setAfficher(true);

        $this->addFlash(
            'notification',
            'L\'article \''.$article->getTitre().'\' est en ligne'
        );

        $entityManager->flush();

        return $this->redirectToRoute('article_liste');
    }

    /**
     * @Route("/article/cacher/{id}", name="article_cacher")
     */
    public function article_cacher($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $article->setAfficher(false);

        $this->addFlash(
            'notification',
            'L\'article \''.$article->getTitre().'\' n\'est plus en ligne'
        );

        $entityManager->flush();

        return $this->redirectToRoute('article_liste');
    }
}
