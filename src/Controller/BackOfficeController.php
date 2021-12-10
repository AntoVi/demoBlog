<?php

namespace App\Controller;

use App\Entity\Catalogue;
use App\Repository\CatalogueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function adminCatalogues(EntityManagerInterface $manager, CatalogueRepository $repoCatalogue): Response
    {
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
       


        return $this->render('back_office/admin_catalogues_html.twig', [
            'colonnes' => $colonnes,
            'catalogues' => $catalogues
        ]);
    }

    

    #[Route('/admin/categories', name: 'app_admin_categorie')]
    public function adminCategories(EntityManagerInterface $manager): Response
    {
       
        

        return $this->render('back_office/admin_categories_html.twig', [

        ]);
    }

    #[Route('/admin/commentaires', name: 'app_admin_commentaire')]
    public function adminCommentaires(EntityManagerInterface $manager): Response
    {
       
        

        return $this->render('back_office/admin_commentaires_html.twig', [

        ]);
    }

    #[Route('/admin/users', name: 'app_admin_user')]
    public function adminUsers(EntityManagerInterface $manager): Response
    {
        
        

        return $this->render('back_office/admin_users_html.twig', [

        ]);
    }
}
