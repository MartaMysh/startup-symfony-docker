<?php

namespace App\Service;
final class PolishNumberParser
{
    public const ONES = [
    'pierwsz' => 1,
    'drug' => 2,
    'trzec' => 3,
    'czwart' => 4,
    'piat' => 5,
    'szost' => 6,
    'siodm' => 7,
    'osm' => 8,
    'dziewiat' => 9,
];

    public const TEENS = [
        'jedenast' => 11,
        'dwunast' => 12,
        'trzynast' => 13,
        'czternast' => 14,
        'pietnast' => 15,
        'szesnast' => 16,
        'siedemnast' => 17,
        'osiemnast' => 18,
        'dziewietnast' => 19,
    ];

    public const TENS = [
        'dziesiat' => 10,
        'dwudziest' => 20,
        'trzydziest' => 30,
        'czterdziest' => 40,
        'piecdziesiat' => 50,
    ];
    public static function parse(string $token): ?int
    {
        foreach (self::TEENS as $root => $value) {
            if (str_starts_with($token, $root)) {
                return $value;
            }
        }

        $result = 0;

        foreach (self::TENS as $root => $value) {
            if (str_starts_with($token, $root)) {
                $result += $value;
                $token = substr($token, strlen($root));
                break;
            }
        }

        foreach (self::ONES as $root => $value) {
            if (str_starts_with($token, $root)) {
                $result += $value;
                break;
            }
        }

        return $result > 0 ? $result : null;
    }
}
