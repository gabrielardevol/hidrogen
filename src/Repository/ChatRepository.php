<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chat>
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function findByUserId(string $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.publishingUser = :userId')
            ->orWhere('c.interestedUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByUsersAndSubject(
        User $interestedUser,
        User $publishingUser,
        string $subjectId
    ): ?Chat {
        return $this->createQueryBuilder('c')
            ->where('c.interestedUser = :interestedUser')
            ->andWhere('c.publishingUser = :publishingUser')
            ->andWhere('c.subjectId = :subjectId')
            ->setParameter('interestedUser', $interestedUser)
            ->setParameter('publishingUser', $publishingUser)
            ->setParameter('subjectId', $subjectId)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
