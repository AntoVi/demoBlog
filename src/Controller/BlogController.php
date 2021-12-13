<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Category;
use App\Entity\Catalogue;
use App\Form\CatalogueType;
use App\Form\CommentairesType;
use App\Repository\CategoryRepository;
use App\Repository\CatalogueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    // Cette méthode permet de selectionner toutes les catégories de la BDD mais ne possède pas de route
    // Les catégories seront intégrées dans base.html.twig
    public function allCategory(CategoryRepository $repoCategory)
    {
        $categorys = $repoCategory->findAll();

        return $this->render('blog/categorys_list.html.twig', [
            'categorys' => $categorys
        ]);
    }


    #[Route('/blog', name: 'blog')]
    #[Route('/blog/categorie/{id}', name: 'blog_categorie')]
    public function blog(CatalogueRepository $repoCatalogue, Category $category = null): Response
    {
        // dd($category->getCatalogues());
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

        

        //Si la condition retourne TRUE, cela veut dire que l'utilisateur a cliqué sur le lien d'une catégorie
        // dans la nav et par conséquent, $category contient une catégorie stocké en BDD, alors on entre dans le IF
        if($category)
        {
            // Grace aux relations bi-directgionnelle, lorsque nous selectionnons une catégorie en BDD, nous avons
            // accès automatiquement à tous les articles liés à cette catégorie
            // GetCatalogues() retourne un array multi contenant tous les articles liés à la catéghorie transmise dans l'URL
            $catalogue = $category->getCatalogues();
        }
        else // Sinon aucune catégorie n'est transmise en URL, alors on selectionne tous les articles dans la BDD
        {
            //findAll() : méthode issue de la classe ArticleRepository permettant 
            // de selectionner l'ensemble de la table SQL et de récupérer un tableau
            // multi contenant l'ensemble des articles stocké en BDD

            $catalogue = $repoCatalogue->findAll(); // SELECT  * FROM catalogue + FETCH_ALL
        }

        
       
        // dd($catalogue);

        return $this->render('blog/blog.html.twig', [
            'catalogues' => $catalogue // on transmet au template les catalogues en BDD afin que twig traite l'affichage
        ]);
    }

     # Méthode permettant d'insérer / modifier un article en BDD
     #[Route('/blog/new', name: 'blog_create')]
     #[Route('/blog/{id}/edit', name: 'blog_edit')]
     public function blogCreate(Catalogue $catalogue = null, Request $request, EntityManagerInterface $manager,
     SluggerInterface $slugger): Response 
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

        // Si la condition IF retourne TRUE, cela veut dire que $catalogue contient un article stocké
        // en BDD, on stock la photo actuelle de l'article dans la variable $photoActuelle
        
        
        if($catalogue)
        {
            $photoActuelle = $catalogue->getPhoto();
        }

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


        // Si le formulaire a bien été validé (isSubmitted) et que l'objet entité est correctement remplit
        //(isValid) alors on entre dans le condition IF 
        if($formCatalogue->isSubmitted() && $formCatalogue->isValid())
        {
            // Le seul setter que l'on appel de l'entité, c'est celui de la date
            // puisqu'il n'y a pas de champ 'date' dans le formulaire

            // Si l'article ne possède pas d'ID, c'est une insertion, alors on entre 
            //dans la condition IF et on génère une date de catalogue

            if(!$catalogue->getId())
                $catalogue->setDate(new \DateTime());


            // DEBUT Traitement DE LA PHOTO 
            // On récupère toutes les informations de l'image uploadée dans le formulaire 
            $photo = $formCatalogue->get('photo')->getData();
            

            if($photo) // si une photo est uploadé dans le formulaire, on entre dans le IF et on 
            // traite l'image 
            {
                // On récupère le nom d'origine de la photo
                $nomOriginePhoto = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
              

                // cela est nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL 
                $secureNomPhoto = $slugger->slug($nomOriginePhoto);

                //                  Pirate              -   88484848    .       jpg
                $nouveauNomFichier = $secureNomPhoto . '-' . uniqid() . '.' . $photo->guessExtension();

                

                try // on tente de copier l'image dans le dossier
                {
                    // On copie l'image vers le bon chemin, vers le bon dossier 'public/uploads/photos'
                    // (services.yaml)
                    $photo->move(
                        $this->getParameter('photo_directory'),
                        $nouveauNomFichier
                    );
                }
                catch(FileException $e)
                {

                }
                // on insère le nom de l'image dans la bdd 
                $catalogue->setPhoto($nouveauNomFichier);
            }
            else // Sinon aucune image n'a été uploadé, on renvoi dans la bdd la photo actuelle de 
            // l'article 
            {
                // Si la photo actuelle est définit en BDD, alors en cas de modifications, si on ne change pas 
                // de photo, on renvoi la photo actuelle en BDD
                if(isset($photoActuelle))
                    $catalogue->setPhoto($photoActuelle);
                else 
                    // Sinon aucune photo n'a été uploadé, on envoi la valeur NULL en BDD pour la photo
                    $catalogue->setPhoto(null);
            }

            // FIN TRAITEMENT PHOTO

            // message de validation en session 
            if(!$catalogue->getId())
                $txt = "enregistré";
            else 
                $txt = "modifié";

            //méthode permettant d'enregistrer des messages utilisateurs accessibles en session
            $this->addFlash('success', "L'article a été $txt avec succès !");

            $manager->persist($catalogue); 
            $manager->flush();

            // Une fois l'insertion/modification executée en BDD, on redirige
            // l'internaute vers le détail de l'article, on transmet l'id à fournir dans 
            // l'url en 2eme paramètre de la méthode redirectToRoute()
            return $this->redirectToRoute('blog_show', [
                'id' => $catalogue->getId()
            ]);
        }

         return $this->render('blog/blog_create.html.twig', [
             'formCatalogue' => $formCatalogue->createView(), // on transmet le formulaire au template 
             // afin de pouvoir l'afficher avec Twig
             // createView() retourne un petit objet qui représente l'affichage du formulaire, 
             // on le récupère dans le template blog_create.html.twig
             'editMode' => $catalogue->getId(),
             'photoActuelle' => $catalogue->getPhoto()
         ]);
     }

    // # Méthode permettant d'afficher le détail d'un article
    // On définit un route 'paramétrée' {id}, ici la route permet de recevoir l'id d'un article stocké en BDD 

    //      / blog/5


    


    #[Route('/blog/{id}', name:'blog_show')]
    public function blogShow(Catalogue $catalogue, Request $request, EntityManagerInterface $manager ): Response 
    {

        /*

            Ici, nous envoyons un ID dans l'URL et nous imposons en argument un objet issu de
            l'entité Catalogue donc la table SQL
            Donc Symfony est capable de selectionner en BDD l'article en fonction de l'ID passé dans l'URL 
            et de l'envoyer automatiquement en argument de la méthode blogShow() dans la variable 
            de reception $catalogue 

        */

            // Cette méthode mise à disposition retourne un objet app/entity/article contnant toute les
            //données de l'utilisateur authentifié sur le site
          
            $user = $this->getUser();
            // dd($user);
            $comments = new Comment;

        $formCommentaires = $this->createForm(CommentairesType::class, $comments ,[
            'commentFormFront' => true // on indique dans quelle condition IF on entre dans le fichier
            // 'App\form\CommentType' et quel formulaire nous affichons 
        ]); 

        $formCommentaires->handleRequest($request);


        
        if($formCommentaires->isSubmitted() && $formCommentaires->isValid())
        {
                // getUser() : méthode de Symfony qui retourne un objet (App\Entity\User) contenant
                // les informations de l'utilisateur authentifié sur le blog
                $user = $this->getUser();
            
                $comments->setDate(new \DateTime());

                $comments->setArticle($catalogue); // on relie le commentaire le catalogue

                $comments->setAuteur( $user->getPrenom() . ' ' . $user->getNom());
                

                $manager->persist($comments); 
                $manager->flush();

                $this->addFlash('success', "Le commentaire a été posté avec succès !");

                return $this->redirectToRoute('blog_show', [
                    'id' => $catalogue->getId()
                ]);

             


           
        }



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
            'catalogue' => $catalogue, // on transmet au template l'article selectionné en BDD afin que 
            // twig puisse traiter et afficher les données sur la page
            'formCommentaires' => $formCommentaires->createView(),
            'user' => $user
        ]);
    }

   
}
