<?php

namespace App\Tests\School\Domain;

use App\School\Domain\SchoolMatcher;
use App\School\Entity\School;
use PHPUnit\Framework\TestCase;

class SchoolMatcherTest extends TestCase
{
    private function createSchool(string $name): School
    {
        $school = new School();
        $school->setOfficialName($name);
        return $school;
    }

    public function test_exact_match(): void
    {
        $matcher = new SchoolMatcher();

        $schools = [
            $this->createSchool('Liceum Ogólnokształcące im. Adama Mickiewicza'),
        ];

        $result = $matcher->match(
            'Liceum Ogólnokształcące im. Adama Mickiewicza',
            $schools
        );

        $this->assertNotNull($result);
        $this->assertSame(
            'Liceum Ogólnokształcące im. Adama Mickiewicza',
            $result->getOfficialName()
        );
    }


    public function test_fuzzy_match_low_threshold(): void
    {
        $matcher = new SchoolMatcher();

        $schools = [
            $this->createSchool('I Liceum Ogólnokształcące im. Adama Mickiewicza'),
            $this->createSchool('XIV Liceum Ogólnokształcące im. Stanisława Staszica'),
            $this->createSchool('Zespół Szkół Elektronicznych i Informatycznych'),
            $this->createSchool('Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego'),
            $this->createSchool('II Liceum Ogólnokształcące im. Marii Konopnickiej'),
            $this->createSchool('Technikum Informatyczne nr 1'),
            $this->createSchool('Liceum Ogólnokształcące im. Mikołaja Kopernika'),
            $this->createSchool('Zespół Szkół Technicznych i Ogólnokształcących'),
            $this->createSchool('III Liceum Ogólnokształcące im. Juliusza Słowackiego'),
            $this->createSchool('Liceum Ogólnokształcące im. Henryka Sienkiewicza'),
            $this->createSchool('Technikum Mechatroniczne')
        ];

        $aliases = [
            // I LO Mickiewicza
            'LO Mickiewicza' => 'I Liceum Ogólnokształcące im. Adama Mickiewicza',
            'I LO' => 'I Liceum Ogólnokształcące im. Adama Mickiewicza',
            'Pierwsze LO' => 'I Liceum Ogólnokształcące im. Adama Mickiewicza',

            // XIV LO Staszica
            'Staszic' => 'XIV Liceum Ogólnokształcące im. Stanisława Staszica',
            'XIV LO' => 'XIV Liceum Ogólnokształcące im. Stanisława Staszica',
            '14 LO' => 'XIV Liceum Ogólnokształcące im. Stanisława Staszica',
            'Staszica' => 'XIV Liceum Ogólnokształcące im. Stanisława Staszica',

            // ZSEiI
            'ZSEI' => 'Zespół Szkół Elektronicznych i Informatycznych',
            'Elektronik' => 'Zespół Szkół Elektronicznych i Informatycznych',
            'ZSEiI' => 'Zespół Szkół Elektronicznych i Informatycznych',

            // V LO Wybickiego
            'V LO' => 'Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego',
            'Piąte LO' => 'Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego',
            'Wybicki' => 'Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego',
            'LO 5' => 'Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego',

            // II LO Konopnickiej
            'Konopnicka' => 'II Liceum Ogólnokształcące im. Marii Konopnickiej',
            'Drugie LO' => 'II Liceum Ogólnokształcące im. Marii Konopnickiej',
            'II LO' => 'II Liceum Ogólnokształcące im. Marii Konopnickiej',
            'LO Konopnickiej' => 'II Liceum Ogólnokształcące im. Marii Konopnickiej',

            // Technikum Informatyczne nr 1
            'TI 1' => 'Technikum Informatyczne nr 1',
            'Pierwsze Informatyczne' => 'Technikum Informatyczne nr 1',
//            'Technikum IT' => 'Technikum Informatyczne nr 1',

            // LO Kopernika
            'Kopernik' => 'Liceum Ogólnokształcące im. Mikołaja Kopernika',
            'LO Kopernika' => 'Liceum Ogólnokształcące im. Mikołaja Kopernika',

            // ZSTiO
            'ZSTiO' => 'Zespół Szkół Technicznych i Ogólnokształcących',
            'Techniczne i Ogólnokształcące' => 'Zespół Szkół Technicznych i Ogólnokształcących',
            'ZSTO' => 'Zespół Szkół Technicznych i Ogólnokształcących',

            // III LO Słowackiego
            'Słowacki' => 'III Liceum Ogólnokształcące im. Juliusza Słowackiego',
            'Trzecie LO' => 'III Liceum Ogólnokształcące im. Juliusza Słowackiego',
            'III LO' => 'III Liceum Ogólnokształcące im. Juliusza Słowackiego',
            'LO Słowackiego' => 'III Liceum Ogólnokształcące im. Juliusza Słowackiego',

            // LO Sienkiewicza
            'Sienkiewicz' => 'Liceum Ogólnokształcące im. Henryka Sienkiewicza',
            'LO Sienkiewicza' => 'Liceum Ogólnokształcące im. Henryka Sienkiewicza',
            'Sienkiewicz LO' => 'Liceum Ogólnokształcące im. Henryka Sienkiewicza',

            // Technikum Mechatroniczne
            //'Mechatronika' => 'Technikum Mechatroniczne',
            'TM' => 'Technikum Mechatroniczne',
            //'Tech Mechatroniczne' => 'Technikum Mechatroniczne',

            // LO Żeromskiego
            'Żeromski' => 'Liceum Ogólnokształcące im. Stefana Żeromskiego',
            'LO Żeromskiego' => 'Liceum Ogólnokształcące im. Stefana Żeromskiego',
            'Zeromski' => 'Liceum Ogólnokształcące im. Stefana Żeromskiego',
        ];

        foreach ($aliases as $input => $expectedOfficialName) {

            $result = $matcher->match($input, $schools);
            $this->assertNotNull($result);
            $this->assertSame($expectedOfficialName, $result->getOfficialName());
        }
    }

    public function test_no_match(): void
    {
        $matcher = new SchoolMatcher();

        $schools = [
            $this->createSchool('Technikum Mechaniczne'),
        ];

        $result = $matcher->match('Liceum w Warszawie', $schools);

        $this->assertNull($result);
    }
}
