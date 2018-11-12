<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Job::class);
    }

    public function findLatest() {
        return $this->createQueryBuilder('j')
                        ->orderBy('j.id', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    public function findByTimespan(\DateTime $from, \DateTime $to) {
        return $this->createQueryBuilder('j')
                        ->orderBy('j.id', 'DESC')
                        ->where('j.dateIncoming >= :from')
                        ->andWhere('j.dateIncoming <= :to')
                        ->setParameter('from', $from)
                        ->setParameter('to', $to)
                        ->getQuery()
                        ->getResult();
    }

    public function findOpenJobsForUser(User $user) {
        return $this->createQueryBuilder('j')
            ->orderBy('j.id', 'DESC')
            ->join('j.arrangers', 'user')
            ->where(':user MEMBER OF j.arrangers')
            ->andWhere('j.invoiceNumber IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
