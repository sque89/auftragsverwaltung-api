<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;;

/**
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Job::class);
    }

    public function findLatest() {
        return $this->createQueryBuilder('j')
                        ->orderBy('j.id', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    public function findByTimespan(\DateTime $from, \DateTime $to, $groupBy = null) {
        $query = $this->createQueryBuilder('j')
                ->where('j.dateIncoming >= :from')
                ->andWhere('j.dateIncoming <= :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);

        if ($groupBy) {
            $query->select('count(j.id) as count, j.' . $groupBy)->groupBy('j.' . $groupBy);
        } else {
            $query->orderBy('j.id', 'DESC');
        }

        return $query->getQuery()
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

    public function getOpenJobCount() {
        return (int) $this->createQueryBuilder('j')
                        ->select('count(j.id)')
                        ->where('j.invoiceNumber IS NULL')
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getOpenJobOverdueCount() {
        return (int) $this->createQueryBuilder('j')
                        ->select('count(j.id)')
                        ->where('j.invoiceNumber IS NULL')
                        ->andWhere('j.dateDeadline > CURRENT_TIMESTAMP()')
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getOpenJobIntimeCount() {
        return (int) $this->createQueryBuilder('j')
                        ->select('count(j.id)')
                        ->where('j.invoiceNumber IS NULL')
                        ->andWhere('j.dateDeadline <= CURRENT_TIMESTAMP()')
                        ->getQuery()
                        ->getSingleScalarResult();
    }

}
