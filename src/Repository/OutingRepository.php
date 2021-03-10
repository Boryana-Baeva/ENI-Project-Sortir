<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Campus;
use App\Entity\Outing;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints;

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

    /**
     * @return Outing[] Returns an array containing the searched events
     */
    public function findSearched(SearchData $searchData, array $searchParams)
    {
        $query = $this
            ->createQueryBuilder('o')
            ->select('o');

        if (!empty($searchData->organizer))
        {
            $query = $query
                ->andWhere('o.organizer = :organizer')
                ->setParameter('organizer', $searchParams['connectedUser']);
        }

        if (!empty($searchData->pastOutings))
        {
            $query = $query
                ->andWhere('o.startDateTime < :startDateTime')
                ->setParameter('startDateTime', new \DateTime());
        }

        if (!empty($searchData->subscribed))
        {
            $query = $query
                ->andWhere(':user MEMBER OF o.participants')
                ->setParameter('user', $searchParams['connectedUser'] );
        }

        if (!empty($searchData->unsubscribed))
        {
            $query = $query
                ->andWhere(':user NOT MEMBER OF o.participants')
                ->setParameter('user', $searchParams['connectedUser'] );
        }

        if (!empty($searchData->campus))
        {
            $query = $query
                ->andWhere('o.campus = :campus')
                ->setParameter('campus', $searchParams['campus']);
        }

        if (!empty($searchData->q))
        {
            $query = $query
                ->andWhere('o.name LIKE :name')
                ->setParameter('name', "%{$searchParams['outingName']}%");
        }

        if (!empty($searchData->minDate) && !empty($searchData->maxDate))
        {
            $minDateArray = $searchParams['minDate'];
            $maxDateArray = $searchParams['maxDate'];
            $minDateString = (String)$minDateArray['year'] . '-' . (String)$minDateArray['month'] . '-' . (String)$minDateArray['day'];
            $maxDateString = (String)$maxDateArray['year'] . '-' . (String)$maxDateArray['month'] . '-' . (String)$maxDateArray['day'];
            $minDate = new Datetime($minDateString);
            $maxDate = new Datetime($maxDateString);

            $query = $query
                ->andWhere('o.startDateTime BETWEEN :minDate AND :maxDate')
                ->setParameter('minDate', $minDate)
                ->setParameter('maxDate', $maxDate)
                ;
        }
        elseif (!empty($searchData->minDate))
        {
            $minDateArray = $searchParams['minDate'];
            $minDateString = (String)$minDateArray['year'] . '-' . (String)$minDateArray['month'] . '-' . (String)$minDateArray['day'];

            $minDate = new Datetime($minDateString);
            $query = $query
                ->andWhere('o.startDateTime >= :startDateTime')
                ->setParameter('startDateTime', $minDate);
        }
        elseif (!empty($searchData->maxDate))
        {
            $maxDateArray = $searchParams['maxDate'];
            $maxDateString = (String)$maxDateArray['year'] . '-' . (String)$maxDateArray['month'] . '-' . (String)$maxDateArray['day'];
            $maxDate = new Datetime($maxDateString);
            $query = $query
                ->andWhere('o.startDateTime <= :startDateTime')
                ->setParameter('startDateTime', $maxDate);
        }

        return $query->getQuery()->getResult();
    }

    public function countAllOutings()
    {
        $queryBuilder = $this->createQueryBuilder('out');
        $queryBuilder->select('COUNT(out.id) as value');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

}
