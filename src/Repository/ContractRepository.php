<?php

namespace App\Repository;

use App\Entity\Contract;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contract|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contract|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contract[]    findAll()
 * @method Contract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Contract $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Contract $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return QueryBuilder Returns a QueryBuilder to find contracts with start and end dates
     */
    public function findByAwardDateQB(?\DateTime $startDate = null, ?\DateTime $endDate = null) {
        $qb = $this->createQueryBuilder('c');
        if ( null !== $startDate ) {
            $qb->andWhere('c.awardDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }
        if (null !== $endDate) {
            $qb->andWhere('c.awardDate < :endDate')
                ->setParameter('endDate', $endDate->add(DateInterval::createFromDateString('1 day')));
        }
        $qb->orderBy('c.awardDate', 'ASC');
        return $qb;
    }

    /**
     * @return Contract[] Returns an array of Contract objects
     */
    public function findByAwardDate (?\DateTime $startDate = null, ?\DateTime $endDate = null) {
        return $this->findByAwardDateQB($startDate, $endDate)->getQuery()->getResult();
    }

    /**
     * @return Contract|null Return a Contract by code, if not found returns null
     */
    public function findByCode (string $code) {
        return $this->findOneBy([
            'code' => $code,
        ]);
    }

    // /**
    //  * @return Contract[] Returns an array of Contract objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Contract
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
