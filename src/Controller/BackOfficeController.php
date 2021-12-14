<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Catalogue;
use App\Entity\Category;
use App\Form\CatalogueType;
use App\Form\CategoryType;
use App\Form\CommentairesType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Repository\CatalogueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BackOfficeController extends AbstractController
{
    // Méthode qui affiche la page home du backoffice
    #[Route('/admin', name: 'app_admin')]
    public function adminHome(): Response
    {
        return $this->render('back_office/index.html.twig');
    }

    //  Méthode qui affiche la page Home du backoffice 
    #[Route('/admin/catalogues', name: 'app_admin_catalogue')]
    #[Route('/admin/catalogues/{id}/remove', name: 'app_admin_catalogue_remove')]

    public function adminCatalogues(EntityManagerInterface $manager, CatalogueRepository $repoCatalogue, Catalogue $catRemove = null): Response
    {

        // dd($catRemove);
        $colonnes = $manager->getClassMetadata(Catalogue::class)->getFieldNames();
        // dd($colonnes);

        /*
            Exo : afficher sous forme de tableau HTML l'ensemble des articles stockés en BDD
            1. Selectionner en BDD l'ensemble de la table 'article' et transmettre le résultat à la méthode render()
            2 . Dans le template 'admin_catalogues.html.twig', mettre en forme l'affichage des articles
            3 . Afficher le nom de la catégorie de chaque article
            4 . Afficher le nombre de commentaire de chaque article 
            5 .Prévoir un lien modification/suppression pour chaque article 

        */

        $catalogues = $repoCatalogue->findAll();


        // Traitement suppression article en BDD 
        if($catRemove)
        {
            // Avant de supprimer l'article dans la bdd, on stock son ID afin de l'intégrer dans 
            // le message de validation de suppression (addFlash)
            $id =$catRemove->getId();

            $manager->remove($catRemove);
            $manager->flush();

            $this->addFlash('success', "Le catalogue n° $id a été supprimé avec succès");

            return $this->redirectToRoute('app_admin_catalogue');
        }
       


        return $this->render('back_office/admin_catalogues_html.twig', [
            'colonnes' => $colonnes,
            'catalogues' => $catalogues
        ]);
    }

    #[Route('/admin/catalogues/new', name: 'app_admin_catalogues_create')]
    #[Route('/admin/catalogues/{id}/edit', name: 'app_admin_catalogues_form')]
     public function adminCataloguesForm(Catalogue $catalogue = null, Request $request, EntityManagerInterface $manager,
     SluggerInterface $slugger): Response 
     {

            /*
        
        Exo : création d'une nouvelle méthode permettant d'insérer et de modifier 1 catalogue en BDD
        1. créer une route '/admin/article/add' (name : app_admin_article_add)
        2. créer la méthode adminArticleForm()
        3. créer un novueau template 'admin_article_form.html.twig'
        4 . Importer et créer le formulaira au sein de la méthode adminCatalogueForm() createForm
        5 Afficher le formulaire sur le template 'admin_article_form.html.twig'
        6. gérer l'upload de la photo 
        7. dans la méthode adminArticleForm

        */
        
        if($catalogue)
        {
            $photoActuelle = $catalogue->getPhoto();
        }

        if(!$catalogue)
        {
            $catalogue = new Catalogue;
        }
        

       

        $formAdminCatalogue = $this->createForm(CatalogueType::class, $catalogue); 

        $formAdminCatalogue->handleRequest($request);

        if($formAdminCatalogue->isSubmitted() && $formAdminCatalogue->isValid())
        {
            

            if(!$catalogue->getId())
                $catalogue->setDate(new \DateTime());


            // DEBUT Traitement DE LA PHOTO 
            $photo = $formAdminCatalogue->get('photo')->getData();
            

            if($photo)  
            {
                
                $nomOriginePhoto = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
              

                $secureNomPhoto = $slugger->slug($nomOriginePhoto);

        
                $nouveauNomFichier = $secureNomPhoto . '-' . uniqid() . '.' . $photo->guessExtension();

                

                try // try on tente de copier la photo dans le bon dossier
                {
                  
                    $photo->move(
                        $this->getParameter('photo_directory'),
                        $nouveauNomFichier
                    );
                }
                catch(FileException $e)
                {

                }
               // on enregistre la photo en BDD
                $catalogue->setPhoto($nouveauNomFichier);
            }
            else  
            {
                // Si l'article possède une photo mais qu'on ne souhaite pas la modifiée, 
                // alors on entre la condition IF et on renvoi la même photo dans la BDD
                
                if(isset($photoActuelle))
                    $catalogue->setPhoto($photoActuelle);
                else 
                    // Sinon on crée un nouvel article mais on ne souhaite uploadée d'image,
                    // Sinon on crée un nouvel article mais 
                    $catalogue->setPhoto(null);
            }

            // FIN TRAITEMENT PHOTO

            $manager->persist($catalogue); 
            $manager->flush();
            $id = $catalogue->getId();
            $this->addFlash('success', "Le catalogue $id a été modifié avec succès");

            return $this->redirectToRoute('app_admin_catalogue', [
                'id' => $catalogue->getId()
            ]);
        }

         return $this->render('back_office/admin_catalogues_form.html.twig', [
             'formAdminCatalogue' => $formAdminCatalogue->createView(), 
             'editMode' => $catalogue->getId(),
             'photoActuelle' => $catalogue->getPhoto() // renvoi la photo de l'article pour l'afficher en cas de mofidication
         ]);
     }


    #[Route('/admin/categories', name: 'app_admin_categorie')]
    #[Route('/admin/categories/{id}/remove', name: 'app_admin_categorie_remove')]
    public function adminCategories(CategoryRepository $repoCategory, EntityManagerInterface $manager, Category $cateRemove = null): Response
    {
        /*
        Exo : affichage et suppression catégorie 
        1. création d'une nouvelle route '/admin/categories 
        2. création d'une nouvelle méthode adminCategories()
        3. Création d'un nouveau template 
        4 . Selectionner les noms des champs/colonnes de la table Category, les transmettre au template et afficher
        5. Se
        */

        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();
       
        $categories = $repoCategory->findAll(); // Selest * FROM category + FETCH ALL

        if($cateRemove)
        {
            $titreCat = $cateRemove->getTitre();

            // getCatalogues() retourne tous les articles liés à la catégorie, si le résultat est vide, cela veut dire 
            // qu'aucun article n'est lié à la catégorie, on entre dans le IF et on supprime la catégorie

            if($cateRemove->getCatalogues()->isEmpty())
            {
                $this->addFlash('success', "La catégorie $titreCat a été supprimé avec succès");
           
                $manager->remove($cateRemove);
                $manager->flush();
            }
            else  // Sinon, des articles sont encore liés à la catégorie, alors on affiche un message d'erreur à l'utilisateur
            {
                $this->addFlash('danger', "Impossible de supprimer la catégorie '$titreCat' car des article y sont toujours associés");
            }

            return $this->redirectToRoute('app_admin_categorie');




        }
      
        

        return $this->render('back_office/admin_categories_html.twig', [
            'colonnes' => $colonnes,
            'categories' => $categories
        ]);
    }

    #[Route('/admin/categories/add', name: 'app_admin_categorie_add')]
    #[Route('/admin/categories/{id}/edit', name: 'app_admin_categorie_edit')]
    public function adminCategorieForm(Category $category = null, EntityManagerInterface $manager, Request $request): Response
    {

        if(!$category)
        {
            $category = new Category;
        }
        

        $formCategory = $this->createForm(CategoryType::class, $category );

        $formCategory->handleRequest($request);

        if($formCategory->isSubmitted() && $formCategory->isValid())
        {
            if($category->getId())
                $txt = 'modifiée';
            else 
                $txt = 'ajoutée';
            $manager->persist($category);
            $manager->flush();

            $titreCat = $category->getTitre();

            $this->addFlash('success', "La catégorie '$titreCat' a été $txt avec succès");

            return $this->redirectToRoute('app_admin_categorie');
        }



        return $this->render('back_office/admin_categorie_form.html.twig', [
            "formCategory" => $formCategory->createView(),
            'editMode' => $category->getId()
           
        ]);
    }

    #[Route('/admin/commentaires', name: 'app_admin_commentaire')]
    #[Route('/admin/commentaires/{id}/remove', name: 'app_admin_commentaire_remove')]
    public function adminCommentaires( CommentRepository $repoComment,EntityManagerInterface $manager, Comment $comRemove = null): Response
    {
        // Selection du nom des champs/colonnes
        $table = $manager->getClassMetadata(Comment::class)->getFieldNames();

        // dd($table);

        $comments = $repoComment->findAll();
        // dd($comments);

        // SUPPRESSION COMMENTAIRE
        if($comRemove)
        {
            // Stockage de l'auteur du com dans une variable afin d'intégrer le message de validation
            $id = $comRemove->getId();

            $manager->remove($comRemove);

            $manager->flush();

            $this->addFlash('success', "Le commentaire n° $id a été supprimé");


            return $this->redirectToRoute('app_admin_commentaire');
        }



        return $this->render('back_office/admin_commentaires.html.twig', [
            'table' => $table,
            'comments' => $comments
        ]);
    }

    #[Route('/admin/commentaires/{id}/edit', name: 'app_admin_commentaire_edit')]
    public function adminCommentairesEdit( Comment $comment,EntityManagerInterface $manager,Request $request): Response
    {

        
        $formCommentaires = $this->createForm(CommentairesType::class, $comment, [
            'commentFormBack' => true
        ]);

        $formCommentaires->handleRequest($request);

        if($formCommentaires->isSubmitted() && $formCommentaires->isValid())
        {
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', "Le commentaire a été modifié avec succès");

            return $this->redirectToRoute('app_admin_commentaire');
        }
        
       


        return $this->render('back_office/admin_commentaires_edit.html.twig', [
           'formCommentaires' => $formCommentaires->createView(),
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_user')]
    #[Route('/admin/users/{id}/remove', name: 'app_admin_user_remove')]
    public function adminUsers(UserRepository $repoUser, EntityManagerInterface $manager, User $useRemove = null): Response
    {

        $table = $manager->getClassMetadata(User::class)->getFieldNames();
        // dd($table);
        $user = $repoUser->findAll();
        // dd($user);

        if($useRemove)
        {
            $id = $useRemove->getId();

            $manager->remove($useRemove);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur $id a été supprimé avec succés");

            return $this->redirectToRoute('app_admin_user');
        }


        

        return $this->render('back_office/admin_users.html.twig', [
            'table' => $table,
            'user' => $user
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'app_admin_user_edit')]
    public function adminUsersEdit(User $user,EntityManagerInterface $manager, Request $request)
    {
        $formBackUser = $this->createForm(RegistrationFormType::class, $user, [
            'userBack' => true
        ]);

       
      

        $formBackUser->handleRequest($request);

        if($formBackUser->isSubmitted() && $formBackUser->isValid())
        {

           

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "Le rôle a été modifié avec succès");

            return $this->redirectToRoute('app_admin_user');

        }



        return $this->render('back_office/admin_users_edit.html.twig', [
            'formBackUser' => $formBackUser->createView()
        ]);
    }


    
}
