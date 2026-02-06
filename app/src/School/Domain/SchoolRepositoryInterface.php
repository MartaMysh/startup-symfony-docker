<?php

namespace App\School\Domain;

use App\School\Entity\School;

interface SchoolRepositoryInterface
{
    /**
     * @return School[]
     */
    public function findAll(): array;
}
