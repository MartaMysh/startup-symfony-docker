<?php

namespace App\Tests\School\Application;

use App\School\Application\MatchSchoolService;
use App\School\Domain\SchoolMatcher;
use App\School\Domain\SchoolRepositoryInterface;
use App\School\Entity\School;
use PHPUnit\Framework\TestCase;

class MatchSchoolServiceTest extends TestCase
{
    private function createSchool(string $name): School
    {
        $school = new School();
        $school->setOfficialName($name);
        return $school;
    }

    public function test_service_returns_fuzzy_match_with_low_threshold(): void
    {
        $repo = $this->createMock(SchoolRepositoryInterface::class);
        $matcher = new SchoolMatcher();

        $schools = [
            $this->createSchool('Liceum Ogólnokształcące im. Adama Mickiewicza'),
            $this->createSchool('Technikum Elektroniczne'),
        ];

        // repozytorium zwraca listę szkół
        $repo->method('findAll')->willReturn($schools);

        $service = new MatchSchoolService($repo, $matcher);

        $result = $service->match('LO Mickiewicza Warszawa');

        $this->assertNotNull($result);
        $this->assertSame(
            'Liceum Ogólnokształcące im. Adama Mickiewicza',
            $result->getOfficialName()
        );
    }

    public function test_service_returns_null_when_no_reasonable_match(): void
    {
        $repo = $this->createMock(SchoolRepositoryInterface::class);
        $matcher = new SchoolMatcher();

        $schools = [
            $this->createSchool('Technikum Mechaniczne'),
        ];

        $repo->method('findAll')->willReturn($schools);

        $service = new MatchSchoolService($repo, $matcher);

        $result = $service->match('Liceum w Warszawie');

        $this->assertNull($result);
    }
}
