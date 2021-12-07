<?php

namespace App\DataFixtures;

use App\Entity\Catalogue;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CatalogueFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // PHP namespace resolver : extension permettant d'importer les classes 
        // (ctrl + alt + i)
        // la boucle tourne 10 fois afin de créer 10 catalogues fictifs dans la bdd
        for($i = 1; $i <= 10; $i++)
        {
            // pour insérer des données dans la table SQL catalogue, 
            // nous sommes obligé de passer par sa classe Entity correspondante 
            // 'app\Entity\Catalogue', cette classe est le reflet 
            // de la table SQL, elle contient des propriété identiques aux
            // champs/colonnes de la table SQL 
            $catalogue = new Catalogue;

            // On execute tous les setteurs de l'objet afin de remplir les objets
            // et d'ajouter un titre, contenu, image etc ... pour nos 10 catalogues

            $catalogue->setTitre("Titre du catalogue $i")
                        ->setContenu("<p> Lorem ipsum dolor, sit amet consectetur 
                        adipisicing elit. A, iure? </p>")
                        ->setPhoto("https://picsum.photos/id/238/300/500")
                        ->setDate(new \DateTime());

            //Nous faisons appel à l'objet $manager ObjectManager
            // Une classe permet entre autre de manipuler les lignes de la BDD
            //(INSERT , UPDATE, DELETE)
            // persist() : méthode issu de la classe ObjectManager (ORM doctrine) permettant
            // de garder en mémoire les 10 objets $article et de préparer les requetes SQL
            $manager->persist($catalogue); 

            // $r  = $bdd->prepare(" INSERT INTO catalogue VALUES ('$catalogue-> ') ")
        }

    
        // flush() : méthode issu de la classe ObjectManager (ORM doctrine) permettant 
        // véritablement d'executer les requettes SQL en BDD
        $manager->flush();
    }
}
