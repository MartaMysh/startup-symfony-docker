<?php

namespace App\School\UI\Console;

use App\School\Entity\School;
use App\Service\PolishNumberParser;
use App\Service\RomanNumerals;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reindex-schools',
    description: 'Reindexes all schools into MeiliSearch'
)]
class ReindexSchoolsCommand extends Command
{
    public function __construct(
        private Client $client,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $this->client->deleteIndex('schools');
        } catch (\Exception $e) {
            // ignorujemy jeśli nie istnieje
        }

        $this->client->createIndex('schools', [
            'primaryKey' => 'id'
        ]);

        $index = $this->client->index('schools');

        $task = $index->updateSettings([
            'searchableAttributes' => [
                'officialName',
                'normalized',
                'abbr',
                'keywords',
            ],
        ]);

        $this->waitForTask($task['taskUid']);

        $schools = $this->em->getRepository(School::class)->findAll();

        $documents = [];

        foreach ($schools as $school) {
            $name = $school->getOfficialName();
            $normilized = $this->normalize($name);
            $documents[] = [
                'id' => $school->getId(),
                'officialName' => $name,
                'normalized' => $this->normalize($name),
                'abbr' => $this->abbreviation($normilized),
                'keywords' => $this->keywords($normilized),
            ];
        }

        $index->addDocuments($documents);

        $output->writeln('<info>Schools reindexed successfully.</info>');

        return Command::SUCCESS;
    }

    private function waitForTask(int $taskUid): void
    {
        while (true) {
            $task = $this->client->getTask($taskUid);

            if ($task['status'] === 'succeeded') {
                return;
            }

            if ($task['status'] === 'failed') {
                throw new \RuntimeException('MeiliSearch task failed: ' . json_encode($task));
            }

            usleep(100_000); // 100ms
        }
    }

    private function normalize(string $str): string
    {
        $str = mb_strtolower($str);

        $str = strtr($str, [
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
        ]);

        // remove specials
        $str = preg_replace('/[^a-z0-9 ]/u', ' ', $str);

        $str = $this->normalizeNumbers($str);

        return trim(preg_replace('/\s+/', ' ', $str));
    }

    private function normalizeNumbers(string $text): string
    {
        $tokens = explode(' ', $text);

        foreach ($tokens as &$token) {

            // change romans to arabic
            if (preg_match('/^[ivxlcdm]+$/i', $token)) {
                $num = RomanNumerals::toInt($token);
                if ($num !== null) {
                    $token = (string) $num;
                }
            }

            // change words to numbers
            $parsed = PolishNumberParser::parse($token);
            if ($parsed !== null) {
                $token = (string) $parsed;
            }
        }

        return implode(' ', $tokens);
    }

    private function keywords(string $str): array
    {
        $str = preg_replace('/\bnr\.?\b/u', '', $str);
        return explode(' ', $this->normalize($str));
    }

    private function abbreviation(string $str): string
    {
        // 1. remove patron
        $str = $this->stripPatron($str);

        // 2. remove numbers
        $str = preg_replace('/\d+/', '', $str);

        $str = preg_replace('/\bnr\.?\b/u', '', $str);

        // 3. normalize
        $words = explode(' ', $this->normalize($str));
        $abbr = '';
        foreach ($words as $w) {
            if ($w === '') {
                continue;
            }
            $abbr .= mb_substr($w, 0, 1);
        }
        return mb_strtoupper($abbr);
    }

    private function stripPatron(string $str): string
    {
        $patterns = [
            '/\bim\.?\b.*/u',
            '/\bimienia\b.*/u',
        ];

        foreach ($patterns as $pattern) {
            $str = preg_replace($pattern, '', $str);
        }

        return trim($str);
    }

}
