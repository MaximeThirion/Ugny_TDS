<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Image;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{
    /**
     * @Route("/admin/article/creer", name="article_creer")
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

            $article = $form->getData();

            $images = $form->get('images')->getData();

            if ($images) {

                foreach ($images as $fichier) {

                    $image = new Image();

                    $fileName = md5(uniqid()) . '.' . $fichier->guessExtension();

                    $image->setUrl($fileName);

                    $fichier->move(
                        $this->getParameter('article_directory_public'),
                        $fileName
                    );

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($image);

                    $article->addImage($image);
                }
            }

            $mp3 = $form->get('mp3')->getData();
            $mp3Name = md5(uniqid()) . '.' . $mp3->guessExtension();

            $mp3->move(
                $this->getParameter('audio_directory_public'),
                $mp3Name
            );

            $article->setAudio($mp3Name);

            // J'attribue la date de création et de modification à la date de l'instant + le lien de la vidéo
            $article->setCreerA(new \DateTime());
            $article->setModifierA(new \DateTime());

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
     * @Route("/admin/article/modifier/{id}", name="article_modifier")
     */

    // Fonction qui permet de modifier un article
    public function article_modifier(Request $request, $id)
    {
        // J'instancie le manager (fait les opérations via doctrine)
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);

        // J'enregistre l'ancien nom (au cas il existerait deja)
        $lastFileName = [];

        foreach ($article->getImages() as $image) {

            $lastFileName[] = $image->getUrl();
        }

        $lastMp3Name = $article->getAudio();

        // J'instancie le formulaire avec ArticleType (form builder)
        $form = $this->createForm(ArticleType::class, $article);

        // Je demande au formulaire de considérer la requete
        $form->handleRequest($request);

        $images = $form->get('images')->getData();
        $mp3 = $form->get('mp3')->getData();

        // Si le formulaire est soumit et valide, alors
        if ($form->isSubmitted() && $form->isValid()) {

            if ($images) {

                foreach ($article->getImages() as $image) {

                    $lastFileName = $image->getUrl();

                    if (file_exists($this->getParameter('article_directory_public').'/'.$lastFileName)) {
                        // alors supprimes du directory
                        unlink($this->getParameter('article_directory_public').'/'.$lastFileName);
                    }
                    $article->removeImage($image);
                }

                foreach ($images as $fichier) {

                    $image = new Image();

                    $fileName = md5(uniqid()) . '.' . $fichier->guessExtension();

                    $image->setUrl($fileName);

                    $fichier->move(
                        $this->getParameter('article_directory_public'),
                        $fileName
                    );

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($image);

                    $article->addImage($image);
                }
            }

            if ($form->get('mp3')->getData() === null) {
                // J'attribue l'image de l'article à l'ancien nom
                $article->setAudio($lastMp3Name);
            }
            // sinon
            else {
                if (file_exists($this->getParameter('audio_directory_public').'/'.$lastMp3Name)) {
                    // Alors on les supprime
                    unlink($this->getParameter('audio_directory_public').'/'.$lastMp3Name);
                }
                // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
                $mp3Name = md5(uniqid()) . '.' . $mp3->guessExtension();
                $article->setAudio($mp3Name);

                $mp3->move(
                    $this->getParameter('audio_directory_public'),
                    $mp3Name
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
     * @Route("/admin/article/supprimer/{id}", name="article_supprimer")
     */

    // Fonction qui permet de supprimer un article
    public function article_supprimer($id)
    {
        // J'instancie entityManager
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);
        // J'enregistre l'ancien nom (au cas il existerait deja)

        foreach ($article->getImages() as $image) {

            $lastFileName = $image->getUrl();

            if (file_exists($this->getParameter('article_directory_public').'/'.$lastFileName)) {
                // alors supprimes du directory
                unlink($this->getParameter('article_directory_public').'/'.$lastFileName);
            }
            $article->removeImage($image);
        }

        $lastMp3Name = $article->getAudio();

        if (file_exists($this->getParameter('audio_directory_public').'/'.$lastMp3Name)) {
            // Alors on les supprime
            unlink($this->getParameter('audio_directory_public').'/'.$lastMp3Name);
        }
        // Je supprime l'article de la base de donnée
        $entityManager->remove($article);
        // j'execute la requete
        $entityManager->flush();

        // Je redirige sur la route 'article_liste'
        return $this->redirectToRoute('article_liste');
    }

    /**
     * @Route("/admin/article/liste", name="article_liste")
     */

    // Fonction qi permet de lister les articles
    public function article_liste()
    {
        // J'enregistre les articles dans '$listeArticles'
        $listeArticle = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->article_asc();

        // Je créer le rendu twig (template) de ma fonction 'lister'
        return $this->render('article/article_liste.html.twig', [
            'title' => 'Liste',
            'listeArticle' => $listeArticle,
        ]);
    }

    /**
     * @Route("/admin/article/afficher/{id}", name="article_afficher")
     */

    // Fonction qui permet à l'admin de mettre un article en ligne (Visible/Non visible)
    public function article_afficher($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $article->setAfficher(true);
        $article->setModifierA(new \DateTime());

        // Affiche une "notification"
        $this->addFlash(
            'notification',

            // Je notifie à l'admin que l'article au titre "x" est désormais en ligne
            'L\'article \''.$article->getTitre().'\' est en ligne'
        );

        $entityManager->flush();

        return $this->redirectToRoute('article_liste');
    }

    /**
     * @Route("/admin/article/cacher/{id}", name="article_cacher")
     */

    // Fonction qui permet à l'admin de rendre un article hors ligne (non visible sur le site)
    public function article_cacher($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $article->setAfficher(false);
        $article->setModifierA(new \DateTime());

        // Je notifie à l'admin que l'article au titre "x" n'est plus en ligne
        $this->addFlash(
            'notification',
            'L\'article \''.$article->getTitre().'\' n\'est plus en ligne'
        );

        // J'execute la requete
        $entityManager->flush();


        // Redirection de l'admin vers la liste des articles
        return $this->redirectToRoute('article_liste');
    }

    /**
     * @Route("/article", name="article")
     */

    // Affichage des articles
    public function article()
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Affichage des articles par date d'ajout
        $listeArticle = $entityManager->getRepository(Article::class)->article_desc();

        // Affichage des différentes catégories d'article
        $listeCategorie = $entityManager->getRepository(Categorie::class)->findAll();

        // Affichage du template twig qui permet l'affichage des articles des 8 plus récents
        // Affichage des catégories d'article
        return $this->render('article/article_accueil.html.twig', [
            'title' => 'Test',
            'listeArticle' => $listeArticle,
            'listeCategorie' => $listeCategorie,
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_page")
     */

    // Création d'une page article par id
    // Commentaires par utilisateur
    public function article_page($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $utilisateur = $this->getUser();
        $article = $entityManager->getRepository(Article::class)->find($id);

        $commentaire = new Commentaire();

        $form = $this->createForm(CommentaireType::class, $commentaire);

        $form->handleRequest($request);

        // Si le formulaire est soumit et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Alors
            // récupère les données du formulaire (commentaire)
            $commentaire = $form->getData();

            // Définir la date de création du commentaire à la date actuelle (maintenant)
            $commentaire->setCreerA(new \DateTime());

            // Définir la modification d'un commentaire à son edition
            $commentaire->setModifierA(new \DateTime());

            // Défini à quel article est lié le commentaire
            $commentaire->setArticle($article);

            // Défini l'auteur du commentaire à l'utilisateur connecté (session)
            $commentaire->setAuteur($utilisateur);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentaire);

            $entityManager->flush();

            // Renvoie sur le même url afin que l'utilisateur puisse voir son commentaire
            return $this->redirect($request->getUri());
        }

        // Affichage des commentaires liés aux articles
        return $this->render('article/article_page.html.twig', [
            'article' => $article,
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/{idarticle}/commentaire/supprimer/{id}", name="commentaire_supprimer")
     */

    // Permet de supprimer un commentaire
    public function commentaire_supprimer($id, $idarticle)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Retrouve le commentaire par son id
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);

        if ($this->getUser() == $commentaire->getAuteur() || $this->getUser() == $this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($commentaire);
            $entityManager->flush();

            $this->addFlash(
                'notification',
                'Votre commentaire a bien été supprimé'
            );
        }
        else {
            $this->addFlash(
                'notification',
                'Vous ne disposez pas des droits requis pour supprimer ce commentaire'
            );
        }

        // Redirection sur la page d'un article par son id
        return $this->redirectToRoute('article_page', ['id' => $idarticle]);
    }
}