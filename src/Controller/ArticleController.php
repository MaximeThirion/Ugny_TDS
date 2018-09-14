<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{

    /**
     * Fonction qui permet de créer un article
     * @Route("/admin/article/creer", name="article_creer")
     */
    public function articleCreerAction(Request $request)
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
            $mp3 = $form->get('mp3')->getData();

            if ($file) {
                // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
                $fileName = $this->buildFileName($file);
                $file->move(
                    $this->getImageDirectory(),
                    $fileName
                );
                $article->setImage($fileName);
            }

            if ($mp3) {
                $mp3Name = $this->buildFileName($mp3);
                $mp3->move(
                    $this->getAudioDirectory(),
                    $mp3Name
                );
                $article->setAudio($mp3Name);
            }


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
     * Fonction qui permet de modifier un article
     * @Route("/admin/article/modifier/{id}", name="article_modifier")
     */
    public function articleModifierAction(Article $article, Request $request)
    {
        // J'instancie le manager (fait les opérations via doctrine)
        $entityManager = $this->getDoctrine()->getManager();

        // J'enregistre l'ancien nom (au cas il existerait deja)
        $lastFileName = $article->getImage();
        $lastMp3Name = $article->getAudio();

        // J'instancie le formulaire avec ArticleType (form builder)
        $form = $this->createForm(ArticleType::class, $article);

        // Je demande au formulaire de considérer la requete
        $form->handleRequest($request);

        // Si le formulaire est soumit et valide, alors
        if ($form->isSubmitted() && $form->isValid()) {

            // récupération des données
            $this->manageArticleFile($article, $lastFileName, $form->get('file'), 'image');
            $this->manageArticleFile($article, $lastFileName, $form->get('mp3'), 'audio');


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
            'id' => $article->getId(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fonction qui permet de supprimer un article
     * @Route("/admin/article/supprimer/{id}", name="article_supprimer")
     */
    public function articleSupprimerAction($id)
    {
        // J'instancie entityManager
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);
        // J'enregistre l'ancien nom (au cas il existerait deja)
        $lastFileName = $article->getImage();
        $lastMp3Name = $article->getAudio();

        // Supprimer les anciens fichiers
        $this->deleteFile($this->getImageDirectory(), $lastFileName);
        $this->deleteFile($this->getAudioDirectory(), $lastMp3Name);

        // Je supprime l'article de la base de donnée
        $entityManager->remove($article);
        // j'execute la requete
        $entityManager->flush();

        // Je redirige sur la route 'article_liste'
        return $this->redirectToRoute('article_liste');
    }

    /**
     * Fonction qi permet de lister les articles
     * @Route("/admin/article/liste", name="article_liste")
     */
    public function articleListeAction()
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
     * Fonction qui permet à l'admin de mettre un article en ligne (Visible/Non visible)
     * @Route("/admin/article/afficher/{id}", name="article_afficher")
     */
    public function articleAfficherAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $article->setAfficher(true);
        $article->setModifierA(new \DateTime());

        // Affiche une "notification"
        $this->addFlash(
            'notification',

            // Je notifie à l'admin que l'article au titre "x" est désormais en ligne
            'L\'article \'' . $article->getTitre() . '\' est en ligne'
        );

        $entityManager->flush();

        return $this->redirectToRoute('article_liste');
    }

    /**
     * Fonction qui permet à l'admin de rendre un article hors ligne (non visible sur le site)
     * @Route("/admin/article/cacher/{id}", name="article_cacher")
     */
    public function articleCacherAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->find($id);

        $article->setAfficher(false);
        $article->setModifierA(new \DateTime());

        // Je notifie à l'admin que l'article au titre "x" n'est plus en ligne
        $this->addFlash(
            'notification',
            'L\'article \'' . $article->getTitre() . '\' n\'est plus en ligne'
        );

        // J'execute la requete
        $entityManager->flush();


        // Redirection de l'admin vers la liste des articles
        return $this->redirectToRoute('article_liste');
    }

    /**
     * Affichage des articles
     * @Route("/article", name="article")
     */
    public function articleAction()
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
     * Création d'une page article par id
     * Commentaires par utilisateur
     * @Route("/article/{id}", name="article_page")
     */
    public function articlePageAction($id, Request $request)
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
     * Permet de supprimer un commentaire
     * @Route("/article/{id_article}/commentaire/supprimer/{id_commentaire}", name="commentaire_supprimer")
     * @ParamConverter("commentaire", options={"mapping"={"id_commentaire"="id"}})
     */
    public function commentaireSupprimerAction(Commentaire $commentaire, $id_article)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Supprime le commentaire de la base de donnée
        $entityManager->remove($commentaire);
        // Execute la requete
        $entityManager->flush();

        // Redirection sur la page d'un article par son id
        return $this->redirectToRoute('article_page', ['id' => $id_article]);
    }

    /**
     * unlink file
     * @param $parameter
     * @param $fileName
     */
    private function unlink($parameter, $fileName)
    {
        $file = $this->getParameter($parameter) . '/' . $fileName;
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * build uniq name from file
     * @param File $file
     * @return string
     */
    private function buildFileName(File $file = null)
    {
        return $file ? md5(uniqid()) . '.' . $file->guessExtension() : null;

    }

    /**
     * delete articles
     * @param string $imageFile
     * @param string $audioFile
     */
    private function deleteFile($parameter, $file)
    {
        if (file_exists($file)) {
            $this->unlink($parameter, $file);
        }
    }

    private function getAudioDirectory()
    {
        return $this->getParameter('audio_directory');
    }

    private function getImageDirectory()
    {
        return $this->getParameter('article_directory');
    }

    private function manageArticleFile(Article $article, String $oldFilePath, File $newFile, $type = 'image')
    {

        // Si l'input fil est = NULL alors
        if ($newFile === null) {
            // J'attribue l'image de l'article à l'ancien nom
            $article->setImage($oldFilePath);
        } else {

            // build directory
            $directory = $this->{$type . '_directory'};

            // Supprimer les anciens fichiers
            $this->deleteFile($directory, $oldFilePath);

            // Je génère un nom unique
            $fileName = $this->buildFileName($newFile);
            $article->setImage($fileName);

            // Je déplace le fichier uploadé dans le repertoire 'activite_directory' et je lui donne le nom contenu par $fileName
            $newFile->move(
                $directory,
                $fileName
            );
        }
    }


}