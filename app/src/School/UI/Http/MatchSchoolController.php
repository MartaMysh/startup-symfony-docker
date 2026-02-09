<?php

namespace App\School\UI\Http;

use App\School\Domain\SchoolMatcher;
use App\School\Entity\School;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchSchoolController extends AbstractController
{
    #[Route('/api/match-school', name: 'api_match_school', methods: ['POST'])]
    public function match(
        Request $request,
        SchoolMatcher $matcher,
        EntityManagerInterface $em
    ): Response {
        $input = $request->query->get('q');

        $schoolId = $matcher->match($input);

        if (!$schoolId) {
            return $this->json(['match' => null]);
        }

        $school = $em->getRepository(School::class)->find($schoolId);

        return $this->json([
            'match' => $school->getOfficialName(),
            'id' => $school->getId(),
        ]);
    }
}
