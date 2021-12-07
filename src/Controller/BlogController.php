<?php

namespace App\Controller;

use App\Entity\Catalogue;
use App\Form\CatalogueType;
use App\Repository\CatalogueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        // méthode de rendu, en fonction de la route dans l'URL, la méthode render()
        //envoi un template, un rendu sur le navigateur
        return $this->render('blog/home.html.twig',[
            'title' => 'Bienvenue sur le blog symfony',
            'age' => 25
        ]);

    }


    #[Route('/blog', name: 'blog')]
    public function blog(CatalogueRepository $repoCatalogue): Response
    {
        /*

        Injections de dépendances : c'est un des fondements de Symfony, ici notre méthode DEPEND de la classe 
        ArticleRepository pour fonctionner correctement
        Ici Symfony comprend que la méthode blog() attend en argument un objet issu de la classe
        ArticleRepository, automatiquement Symfony envoi une instance de cette classe en argument
        de cette classe
        $repoArticle est un objet issu de la classe ArticleRepository, nous n'avons plus qu'à 
        piocher dans l'objet pour atteindre des méthodes de la classe 

        SYMFONY est une application qui est capable de répondre à un navigateur 
        lorsque celui-ci appel une adresse (ex : localhost:8000/blog), le controller 
        doit être capable d'envoyer un rendu, un template sur le navigateur 

        Ici, lorsque l'on transmet la route '/blog' dans l'URL, cela execute la méthode
        index() dans le controller qui renvoie le template '/blog/index.html.twig' sur
        le navigateur
        */

        // Pour selectionner des données en BDD, nous devons passer par une classe Repository, ces
        // classes permettent uniquement d'executer des requettes de selection SELECT en BDD. Elles contiennent 
        // méthodes mais à disposition par Symfony (findAll(), find(), findBy() etc...)

        // Ici nous devons importer au sein de notre controller la classe 
        // Article Repository pour pouvoir selectionner dans le table Article

        //  $reposCatalogue est un objet issu de la classe CatalogueRepository

        //getRepository() est uné méthode issue de l'objet Doctrine permettant ici d'importer la classe ArticleRepository 
        // $repoCatalogue = $doctrine->getRepository(Catalogue::class);
        // dump() / dd() : outil de debug Symfony
        // dump($repoCatalogue);

        //findAll() : méthode issue de la classe ArticleRepository permettant 
        // de selectionner l'ensemble de la table SQL et de récupérer un tableau
        // multi contenant l'ensemble des articles stocké en BDD
        $catalogue = $repoCatalogue->findAll(); // SELECT  * FROM catalogue + FETCH_ALL
        // dd($catalogue);

        return $this->render('blog/blog.html.twig', [
            'catalogues' => $catalogue // on transmet au template les catalogues en BDD afin que twig traite l'affichage
        ]);
    }

     # Méthode permettant d'insérer / modifier un article en BDD
     #[Route('/blog/new', name: 'blog_create')]
     #[Route('/blog/{id}/edit', name: 'blog_edit')]
     public function blogCreate(Catalogue $catalogue = null, Request $request, EntityManagerInterface $manager): Response 
     {
         // La classe Request de Symfony contient toutes les données 
         // véhiculées par les superglobales ($_GET, $_POST, $_SERVER, $_COOKIE etc...)
         // $request->request : la propriété 'request' de l'objet $request
         //contient toutes les données de $_POST


        // Si les données dans le tableau ARRAY $_POST sont supérieur à 0, alors 
        // on entre dans la condition IF 
        // if(count($_POST) > 0)
        // if($request->request->count() > 0)
        // {
            // dd($request);

            // Pour insérer dans la table SQL 'article', nous avons besoin de son entité correspondante
            // $catalogue = new Catalogue;

            //          ->setTitre($_POST['titre])
            // $catalogue->setTitre($request->request->get('titre'))
            //             ->setContenu($request->request->get('contenu'))
            //             ->setPhoto($request->request->get('photo'))
            //             ->setDate(new \DateTime());

            // dd($catalogue);

            // persist() : méthode issue de l'interface EntityManagerInterface
            // permettant de préparer la requete d'insertion et de en mémoire l'objet / la requete
        //     $manager->persist($catalogue);

        //     //  flush() : méthode issue de l'interface EntityManagerInterface
        //     // permettant véritablement d'executer la requete INSERT en BDD (ORM doctrine)
        //     $manager->flush();
        // }

        // Si la variable est null, cela veut dire que nous sommes sur la route ' /blog/new',
        // On entre dans le IF et on crée une nouvelle instance de l'entité Catalogue
        // Si la variable $catalogue n'est pas null, cela veut dire que nous sommes sur la route
        // '/blog/{id}/edit', nous n'entrons pas dans le IF car $catalogue contient un article de la BDD
        if(!$catalogue)
        {
            $catalogue = new Catalogue;
        }
        

        // $catalogue->setTitre("Victorien chialeur")
        //             ->setContenu("Contenu");

        $formCatalogue = $this->createForm(CatalogueType::class, $catalogue); 

        $formCatalogue->handleRequest($request);

        if($formCatalogue->isSubmitted() && $formCatalogue->isValid())
        {
            // Le seul setter que l'on appel de l'entité, c'est celui de la date
            // puisqu'il n'y a pas de champ 'date' dans le formulaire
            $catalogue->setDate(new \DateTime());


            // dd($catalogue);

            $manager->persist($catalogue); 
            $manager->flush();
        }

         return $this->render('blog/blog_create.html.twig', [
             'formCatalogue' => $formCatalogue->createView() // on transmet le formulaire au template 
             // afin de pouvoir l'afficher avec Twig
             // createView() retourne un petit objet qui représente l'affichage du formulaire, 
             // on le récupère dans le template blog_create.html.twig
         ]);
     }

    // # Méthode permettant d'afficher le détail d'un article
    // On définit un route 'paramétrée' {id}, ici la route permet de recevoir l'id d'un article stocké en BDD 

    //      / blog/5
    #[Route('/blog/{id}', name:'blog_show')]
    public function blogShow(Catalogue $catalogue): Response 
    {

        /*

            Ici, nous envoyons un ID dans l'URL et nous imposons en argument un objet issu de
            l'entité Catalogue donc la table SQL
            Donc Symfony est capable de selectionner en BDD l'article en fonction de l'ID passé dans l'URL 
            et de l'envoyer automatiquement en argument de la méthode blogShow() dans la variable 
            de reception $catalogue 

        */


        // dd($catalogue);
        // On importe la classe CatalogueRepository dans la méthode BlogShow pour selectionner
        // (SELECT) dans la table SQL 'catalogue'
        // $repoCatalogue = $doctrine->getRepository(Catalogue::class);
        // dd($repoCatalogue);

        // find(): méthode issue de la classe CatalogueRepository permettant de selectionner un élément par son ID qu'on lui fournit en argument
        // $catalogue = $repoCatalogue->find($id);
        //  dd($catalogue);

        // L'id transmit dans la route '/blog/5' est transmit automatiquement en argument de la méthode blogshow($id) 
        //dans la variable de réception $id
        // dd($id); //5
        return $this->render('blog/blog_show.html.twig', [
            'catalogue' => $catalogue // on transmet au template l'article selectionné en BDD afin que 
            // twig puisse traiter et afficher les données sur la page
        ]);
    }

   
}
