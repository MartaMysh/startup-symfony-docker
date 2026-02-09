<?php

namespace App\Tests;

use App\School\Domain\SchoolMatcher;
use App\School\Entity\School;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchoolMatcherTest extends KernelTestCase
{
    private SchoolMatcher $matcher;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->matcher = self::getContainer()->get(SchoolMatcher::class);
    }

    /**
     * @dataProvider schoolNameProvider
     */
    public function testMatching(string $input, string $expectedOfficialName)
    {
        $school = $this->matcher->match($input);

        $this->assertInstanceOf(School::class, $school, "Matcher zwrócił null dla: $input");
        $this->assertSame(
            $expectedOfficialName,
            $school->getOfficialName(),
            "Matcher zwrócił złą szkołę dla: $input"
        );
    }

    public static function schoolNameProvider(): array
    {
        return [
            // I LO Mickiewicza
            ["I LO", "I Liceum Ogólnokształcące im. Adama Mickiewicza"],
            ["Pierwsze LO", "I Liceum Ogólnokształcące im. Adama Mickiewicza"],
            ["LO Mickiewicza", "I Liceum Ogólnokształcące im. Adama Mickiewicza"],
            ["Mickiewicz LO", "I Liceum Ogólnokształcące im. Adama Mickiewicza"],

            // XIV LO Staszica
            ["XIV LO", "XIV Liceum Ogólnokształcące im. Stanisława Staszica"],
            ["14 LO", "XIV Liceum Ogólnokształcące im. Stanisława Staszica"],
            ["Staszic", "XIV Liceum Ogólnokształcące im. Stanisława Staszica"],
            ["Staszica", "XIV Liceum Ogólnokształcące im. Stanisława Staszica"],

            // ZSEiI
            ["ZSEI", "Zespół Szkół Elektronicznych i Informatycznych"],
            ["Elektronik", "Zespół Szkół Elektronicznych i Informatycznych"],
            ["ZSEiI", "Zespół Szkół Elektronicznych i Informatycznych"],

            // LO 5 Wybickiego
            ["V LO", "Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego"],
            ["Piąte LO", "Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego"],
            ["LO 5", "Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego"],
            ["Wybicki", "Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego"],

            // II LO Konopnickiej
            ["Drugie LO", "II Liceum Ogólnokształcące im. Marii Konopnickiej"],
            ["II LO", "II Liceum Ogólnokształcące im. Marii Konopnickiej"],
            ["LO Konopnickiej", "II Liceum Ogólnokształcące im. Marii Konopnickiej"],
            ["Konopnicka", "II Liceum Ogólnokształcące im. Marii Konopnickiej"],

            // Technikum Informatyczne nr 1
            ["TI 1", "Technikum Informatyczne nr 1"],
            ["Pierwsze Informatyczne", "Technikum Informatyczne nr 1"],
            ["Technikum IT", "Technikum Informatyczne nr 1"],

            // LO Kopernika
            ["Kopernik", "Liceum Ogólnokształcące im. Mikołaja Kopernika"],
            ["LO Kopernika", "Liceum Ogólnokształcące im. Mikołaja Kopernika"],

            // ZSTiO
            ["ZSTiO", "Zespół Szkół Technicznych i Ogólnokształcących"],
            ["Techniczne i Ogólnokształcące", "Zespół Szkół Technicznych i Ogólnokształcących"],
            ["ZSTO", "Zespół Szkół Technicznych i Ogólnokształcących"],

            // III LO Słowackiego
            ["III LO", "III Liceum Ogólnokształcące im. Juliusza Słowackiego"],
            ["Trzecie LO", "III Liceum Ogólnokształcące im. Juliusza Słowackiego"],
            ["Słowacki", "III Liceum Ogólnokształcące im. Juliusza Słowackiego"],

            // LO Sienkiewicza
            ["Sienkiewicz", "Liceum Ogólnokształcące im. Henryka Sienkiewicza"],
            ["LO Sienkiewicza", "Liceum Ogólnokształcące im. Henryka Sienkiewicza"],

            // Technikum Mechatroniczne
            ["Mechatronika", "Technikum Mechatroniczne"],
            ["TM", "Technikum Mechatroniczne"],
            ["Tech Mechatroniczne", "Technikum Mechatroniczne"],

            // LO Żeromskiego
            ["Żeromski", "Liceum Ogólnokształcące im. Stefana Żeromskiego"],
            ["Zeromski", "Liceum Ogólnokształcące im. Stefana Żeromskiego"],
            ["LO Żeromskiego", "Liceum Ogólnokształcące im. Stefana Żeromskiego"],
        ];
    }
}
