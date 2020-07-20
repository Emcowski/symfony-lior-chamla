<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;


class StatsService {

        private $manager;

        public function __construct(ObjectManager $manager) {
            $this->manager = $manager;
        }

        public function getStats() {
            $users = $this->getUsersCount();
            $ads = $this->getAdsCount();
            $bookings = $this->getBookingsCount();
            $comments = $this->getCommentsCount();

            return compact('users', 'ads', 'bookings', 'comments'); // utiliser compact() permet de créer un tableau automatiquement en nommant les clés, càd en ne répétant pas la même clé valeur. exp 'users' => $users, 'ads' => $ads ...
        }

        /**
         * Retourner nombre d'utilisateurs
         *
         * @return void
         */
        public function getUsersCount() {
            return $this->manager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult(); // getSingleScalarResult() permet d'obtenir le résultat sous la forme d'une variable scalaire simple
        }

        /**
         * Retourner nombre d'annonces'
         *
         * @return void
         */
        public function getAdsCount() {
            return $this->manager->createQuery('SELECT COUNT(a) FROM App\Entity\Ad a')->getSingleScalarResult();
        }

        /**
         * Retourner nombre de réservations
         *
         * @return void
         */
        public function getBookingsCount() {
            return $this->manager->createQuery('SELECT COUNT(b) FROM App\Entity\Booking b')->getSingleScalarResult();
        }

        /**
         * Retourner nombre de commentaires
         *
         * @return void
         */
        public function getCommentsCount() {
            return $this->manager->createQuery('SELECT COUNT(c) FROM App\Entity\Comment c')->getSingleScalarResult();
        }

        /**
         * Retourner les annonces par ordre de note, pour faire tri des meilleures et des moins bonnes
         *
         * @param string $direction
         * @return void
         */
        public function getAdsStats($direction) {
            // Sélectionner les annonces
            return $this->manager->createQuery(
                'SELECT AVG(c.rating) as note, a.title, a.id, u.firstName, u.lastName, u.picture
                FROM App\Entity\Comment c 
                JOIN c.ad a
                JOIN a.author u
                GROUP BY a
                ORDER BY note ' .$direction
            )
            ->setMaxResults(5)
            ->getResult();
        }


}

