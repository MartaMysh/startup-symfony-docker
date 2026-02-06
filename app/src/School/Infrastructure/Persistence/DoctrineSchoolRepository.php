<?php

namespace App\School\Infrastructure\Persistence;

use App\School\Domain\SchoolRepositoryInterface;
use App\School\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineSchoolRepository extends ServiceEntityRepository implements SchoolRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, School::class);
    }

    /**
     * @return School[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }
}
