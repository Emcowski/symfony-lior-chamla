<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Trouver les meilleurs utilisateur
     *
     * @param int $limit
     * @return void
     */
    public function findBestUsers($limit = 2) { // Dans le repository la requête se base sur sa propre entité (ici User), pas besoin de la mettre en param
        return $this->createQueryBuilder('u')
                    ->select('u as user, AVG(c.rating) as avgRatings, COUNT(c) as sumComments')
                    ->join('u.ads', 'a')
                    ->join('a.comments', 'c')
                    ->groupBy('u')
                    ->having('sumComments > 3') // si le user a plus de 3 commentaires (sinon il suffit qu'il ait un commentaire avec 5 étoiles pour être bien placé)
                    ->orderBy('avgRatings', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult(); 
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
