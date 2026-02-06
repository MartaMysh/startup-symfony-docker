<?php

namespace App\School\Domain;

use App\School\Entity\School;

class SchoolMatcher
{
    public function match(string $input, array $schools): ?School
    {
        $inputNorm = $this->normalize($input);
        $inputKeywords = $this->extractKeywords($inputNorm);

        $bestScore = 0.0;
        $best = null;

        foreach ($schools as $school) {

            $name = $school->getOfficialName();
            if (!$name) continue;
            $score = 0;
            $schoolNorm = $this->normalize($name);
            $schoolKeywords = $this->extractKeywords($schoolNorm);

            // 1️⃣ Sprawdzenie numeru szkoły
            if (preg_match('/\b\d+\b/', $inputNorm, $numMatch) &&
                preg_match('/\b\d+\b/', $schoolNorm, $schoolNumMatch) &&
                $numMatch[0] === $schoolNumMatch[0]) {
                $score += 0.3;
            }

            // 2️⃣ Sprawdzenie patrona
            $patronKeywords = $this->extractPatron($name);
            $scorePatron = $this->keywordScore($inputKeywords, $patronKeywords, true);
            if ($scorePatron >= 0.5) {
                $score += 2;
            }

            // 3️⃣ Skróty dynamiczne
            $abbreviations = $this->generateAbbreviations($schoolNorm);
            if ($input === $abbreviations) {
                $score += 1;
            }

            if (levenshtein($input, $abbreviations) <= 1 ||
                str_contains($input, $abbreviations) ||
                str_contains($abbreviations, $input)
            ) {
                $score += 0.25;
            }

            // 4 Sprawdzenie pozostałych keywordów
            $score += $this->keywordScore($inputKeywords, $schoolKeywords);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $school;
            }

        }

        return $bestScore >= 0.5 ? $best : null;
    }

    private function normalize(string $str): string
    {
        // Roman numerals → arabic
        $str = preg_replace_callback('/\b[IVXLCDM]+\b/u', function ($m) {
            return $this->romanToInt($m[0]);
        }, $str);

        $str = mb_strtolower($str);

        // Polish ordinal numbers → arabic
        $ordinals = [
            'pierwsz' => 1,
            'drug' => 2,
            'trzec' => 3,
            'czwart' => 4,
            'piąt' => 5,
            'szóst' => 6,
            'siódm' => 7,
            'ósm' => 8,
            'dziewiąt' => 9,
            'dziesiąt' => 10,
        ];

        $str = preg_replace_callback('/\b[a-ząćęłńóśźż]+\b/u', function ($m) use ($ordinals) {
            foreach ($ordinals as $stem => $num) {
                if (str_starts_with($m[0], $stem)) {
                    return $num;
                }
            }
            return $m[0];
        }, $str);

        return trim(preg_replace('/\s+/', ' ', $str));
    }

    private function extractKeywords(string $str): array
    {
        $words = explode(' ', $str);
        $keywords = [];

        foreach ($words as $w) {
            if ($w === '') continue;

            $keywords[] = $w;

            $clean = preg_replace('/[^a-z0-9ąćęłńóśźż]/u', '', $w);
            if ($clean !== '' && $clean !== $w) {
                $keywords[] = $clean;
            }
        }

        return array_values(array_unique($keywords));
    }

    private function keywordScore(array $input, array $school, bool $isPatron = false): float
    {
        if (empty($input)) return 0.0;

        $matches = 0;

        foreach ($input as $kw) {
            foreach ($school as $skw) {
                // exact match
                if ($kw === $skw) {
                    $matches++;
                    break;
                }

                // fuzzy match dla krótkich tokenów (<=4), nie dla liczb
                if (strlen($kw) <= 4 && strlen($skw) <= 4 && !ctype_digit($kw) && !ctype_digit($skw)) {
                    if (str_contains($skw, $kw) || str_contains($kw, $skw)) {
                        $matches++;
                        break;
                    }
                }

                // fuzzy/prefix match dla długich tokenów
                if (!ctype_digit($kw) && !ctype_digit($skw)) {

                    if (mb_strlen($kw) > 7 && mb_strlen($skw) > 7) {
                        $minLen = min(mb_strlen($kw), mb_strlen($skw));

                        $kw = mb_substr($kw, 0, $minLen);
                        $skw = mb_substr($skw, 0, $minLen);
                    }

                    $maxDistance = $isPatron ?
                        max(1, (int)(strlen(max([$kw, $skw])) * 0.3)) :
                        max(1, (int)(strlen($kw) * 0.2));

                    if (str_contains($skw, $kw) || str_contains($kw, $skw) ||
                        levenshtein($kw, $skw) <= $maxDistance ||
                        str_starts_with($skw, $kw) || str_starts_with($kw, $skw)) {
                        $matches++;
                        break;
                    }
                }
            }
        }
        return $matches / count($input);
    }

    private function romanToInt(string $roman): int
    {
        $roman = strtoupper($roman);
        $map = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
        $value = 0;

        for ($i = 0; $i < strlen($roman); $i++) {
            $current = $map[$roman[$i]] ?? 0;
            $next = ($i + 1 < strlen($roman)) ? ($map[$roman[$i + 1]] ?? 0) : 0;

            if ($next > $current) {
                $value += $next - $current;
                $i++;
            } else {
                $value += $current;
            }
        }

        return $value;
    }

    private function extractPatron(string $schoolName): array
    {
        // Szukamy patrona po "im."
        if (preg_match('/im\.\s+(.+)$/u', $schoolName, $matches)) {
            $patron = $matches[1];
            $words = preg_split('/\s+/u', $patron);
            $twoWords = array_slice($words, 0, 2); // dwa pierwsze słowa patrona
            $normalized = array_map(fn($w) => $this->normalize($w), $twoWords);
            return $normalized;
        }
        return [];
    }


    private function generateAbbreviations(string $name): string
    {
        $abbrs = '';

        // 2️⃣ Usuwamy patrona i numer
        $nameWithoutPatron = preg_replace('/\bim\..+$/u', '', $name); // usuwa "im. Adam Mickiewicz"
        $nameWithoutNumber = preg_replace('/\bnr\s*\d+\b/u', '', $nameWithoutPatron); // usuwa "nr 5"

        // 3️⃣ Tworzymy skrót z pierwszych liter słów
        $words = preg_split('/\s+/u', $nameWithoutNumber);
        $initials = '';
        foreach ($words as $w) {
            if (in_array($w, ['i', 'oraz', 'z', 'szkolnych'])) continue;
            $initials .= mb_substr($w, 0, 1);
        }

        if ($initials) {
            $abbrs .= mb_strtoupper($initials);
        }
        return $abbrs;
    }

}
