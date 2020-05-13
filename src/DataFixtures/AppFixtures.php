<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');

        // GESTION DES USERS
        $users = [];
        $genres = ['male', 'female'];

        for($j = 1; $j <=10; $j++) {
            $user = new User();

            // Fonction randomElement de Faker permet de récupérer un élément au hasard d'un tableau passé en param
            $genre = $faker->randomElement($genres);

            $firstName = $faker->firstname($genre); // sur la fonction firstName de Faker, on peut passer un genre (male ou female) pour obtenir un prénom masculin ou féminin
            $lastName = $faker->lastname;
            $email = $faker->email;
            // Construire l'avatar avec une URL de données
            //$picture = $faker->imageUrl(150,150);
            $picture = 'https://randomuser.me/api/portraits/';
            // Chiffre au hasard pour passer à l'URL
            $pictureId = $faker->numberBetween(1, 99) . 'jpg';
            // Si le genre fourni est masculin ou féminin on ajoute le chiffre au hasard
            $picture .= ($genre == "male" ? 'men/' : 'women/') . $pictureId;
            // Cette méthode encode un string avec l'algorithme choisi dans security.yml ; elle prend en param l'entité sur laquelle on souhaite opérer (ici User) puis en second apram le mot à encoder
            $hash = $this->encoder->encodePassword($user, 'password');
            $intro = $faker->sentence();
            $descr = '<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>';
            //$slug = $faker->sentence();

            $user->setFirstName($firstName)
                 ->setLastName($lastName)
                 ->setEmail($email)
                 ->setPicture($picture)
                 ->setHash($hash)
                 ->setIntroduction($intro)
                 ->setDescription($descr);
                 //->setSlug($slug)

            $manager->persist($user);
            $users[] = $user;
        }
        
        // GESTION DES ANNONCES
        for($i = 1; $i <= 30; $i++) {
            $ad = new Ad();

            //contenu bdd faker : https://github.com/fzaninotto/Faker
            $title = $faker->sentence();
            $coverImage = $faker->imageUrl(1000,350);
            $introduction = $faker->paragraph(2);
            $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';

            // Pour chaque annonce on sélectionne un auteur au hasard :
            $user = $users[mt_rand(0, count($users) - 1)];

            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(40, 200))
                ->setRooms(mt_rand(1, 5))
                ->setAuthor($user);

            for($j = 1; $j <= mt_rand(2, 5); $j++) {
                $image = new Image();

                $image->setUrl($faker->imageUrl())
                      ->setCaption($faker->sentence())
                      ->setAd($ad);

                      $manager->persist($image);
            }
                
            $manager->persist($ad);
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
