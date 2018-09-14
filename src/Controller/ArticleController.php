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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{

    private $audio_directory;
    private $image_directory;

    public function __construct()
    {
        $this->audio_directory = $this->getParameter('audio_directory');
        $this->image_directory = $this->getParameter('article_directory');
    }

    /**
     * @Route("/admin/article/creer", name="article_creer")
     */
    // Fonction qui permet de créer un article
    public function articleCreer(Request $request)
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

            // Je génère un nom unique et j'y concat' l'extension d'origine du fichier uploadé
            $fileName = $this->buildFileName($file);
            $mp3Name = $this->buildFileName($mp3);

            // Je déplace le fichier uploadé dans le repertoire 'article_directory' et je lui donne le nom contenu par $fileName
            $file->move(
                $this->getParameter($this->image_directory),
                $fileName
            );

            $mp3->move(
                $this->getParameter($this->audio_directory),
                $mp3Name
            );

            // J'attribue le chemin relatif de l'acitivité contenu dans '$fileName'
            $article->setImage($fileName);
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
    public function articleModifier(Request $request, $id)
    {
        // J'instancie le manager (fait les opérations via doctrine)
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);

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
            $file = $form->get('file')->getData();
            $mp3 = $form->get('mp3')->getData();

            // Si l'input fil est = NULL alors
            if ($file === null) {
                // J'attribue l'image de l'article à l'ancien nom
                $article->setImage($lastFileName);
            } else {

                // Supprimer les anciens fichiers
                $this->deleteFile($lastFileName);

                // Je génère un nom unique
                $fileName = $this->buildFileName($file);
                $article->setImage($this->buildFileName($file));

                // Je déplace le fichier uploadé dans le repertoire 'activite_directory' et je lui donne le nom contenu par $fileName
                $file->move(
                    $this->getParameter($this->image_directory),
                    $fileName
                );
            }

            if ($mp3 === null) {
                // J'attribue l'audio de l'article à l'ancien nom
                $article->setAudio($lastMp3Name);
            } // sinon
            else {

                // Supprimer les anciens fichiers
                $this->deleteFile($lastMp3Name);

                $mp3Name = $this->buildFileName($mp3);
                $article->setAudio($mp3Name);

                $mp3->move(
                    $this->getParameter($this->audio_directory),
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
    public function articleSupprimer($id)
    {
        // J'instancie entityManager
        $entityManager = $this->getDoctrine()->getManager();

        // Je vais chercher dans Repository l'article possedant cet id
        $article = $entityManager->getRepository(Article::class)->find($id);
        // J'enregistre l'ancien nom (au cas il existerait deja)
        $lastFileName = $article->getImage();
        $lastMp3Name = $article->getAudio();

        // Supprimer les anciens fichiers
        $this->deleteFile($this->image_directory, $lastFileName);
        $this->deleteFile($this->audio_directory, $lastMp3Name);

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
    public function articleListe()
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
    public function articleAfficher($id)
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
     * @Route("/admin/article/cacher/{id}", name="article_cacher")
     */

    // Fonction qui permet à l'admin de rendre un article hors ligne (non visible sur le site)
    public function articleCacher($id)
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
    public function articlePage($id, Request $request)
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
     * @Route("/article/{id_article}/commentaire/supprimer/{id_commentaire}", name="commentaire_supprimer")
     * @ParamConverter("commentaire", options={"mapping"={"id_commentaire"="id"}})
     */

    // Permet de supprimer un commentaire
    public function commentaireSupprimer(Commentaire $commentaire, $id_article)
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
    private function buildFileName(File $file)
    {
        return md5(uniqid()) . '.' . $file->guessExtension();

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
}