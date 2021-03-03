<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Outing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Outing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Outing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Outing[]    findAll()
 * @method Outing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OutingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outing::class);
    }

    // /**
    //  * @return Outing[] Returns an array of Outing objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Outing
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Outing[] Returns an array containing the searched events
     */
    public function findSearched(SearchData $searchData, UserInterface $organizer=null, Outing $outing)
    {
        $query = $this
            ->createQueryBuilder('o')
            ->select('o');

        if (!empty($searchData->organizer))
        {
            $query = $query
                ->andWhere('o.organizer = :organizer')
                ->setParameter('organizer', $organizer);
        }

        if (!empty($searchData->pastOutings))
        {
            $query = $query
                ->andWhere('o.startDateTime < :startDateTime')
                ->setParameter('startDateTime', new \DateTime());
        }

        if (!empty($searchData->participants))
        {
            $query = $query
                ->andWhere('o.participants = :participants')
                ->setParameter('participants', $outing->getParticipants());
        }

        return $query->getQuery()->getResult();
    }

}
