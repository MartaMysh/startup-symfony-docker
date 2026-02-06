<?php

namespace App\School\Application;

use App\School\Domain\SchoolMatcher;
use App\School\Domain\SchoolRepositoryInterface;
use App\School\Entity\School;

class MatchSchoolService
{
    public function __construct(
        private SchoolRepositoryInterface $repository,
        private SchoolMatcher $matcher
    ) {}

    public function match(string $name): ?School
    {
        $schools = $this->repository->findAll();
        return $this->matcher->match($name, $schools);
    }
}
