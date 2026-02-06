<?php

namespace App\School\UI\Http;

use App\School\Application\MatchSchoolService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MatchSchoolController
{
    #[Route('/api/match-school', name: 'api_match_school', methods: ['POST'])]
    public function match(Request $request, MatchSchoolService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $name = $data['name'] ?? $request->get('name');

        if (!$name) {
            return new JsonResponse(['error' => 'Missing "name"'], 400);
        }

        $school = $service->match($name);

        if (!$school) {
            return new JsonResponse(['match' => null], 200);
        }

        return new JsonResponse([
            'match' => [
                'id'           => $school->getId(),
                'officialName' => $school->getOfficialName(),
                'city'         => $school->getCity(),
                'type'         => $school->getType(),
            ],
        ], 200);
    }
}
