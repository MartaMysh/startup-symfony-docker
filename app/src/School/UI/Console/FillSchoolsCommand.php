<?php

namespace App\School\UI\Console;

use App\School\Entity\School;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fill-schools',
    description: 'Import szkół z pliku XLSX',
)]
class FillSchoolsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Importuje szkoły z pliku XLSX')
            ->addArgument('file', InputArgument::REQUIRED, 'Ścieżka do pliku XLSX');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if ($filePath) {
            $io->note(sprintf('You passed an argument: %s', $filePath));
        }

        if (!file_exists($filePath)) {
            $io->note('Plik nie istnieje!');
            return Command::FAILURE;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Pomijamy nagłówki
        foreach (array_slice($rows, 1) as $row) {

            $officialName = $row[0];
            $city = $row[2];
            $type = $row[3];

            if (empty($officialName)) {
                continue;
            }
            $school = new School();
            $school
                ->setOfficialName($officialName)
                ->setCity($city)
                ->setType($type);

            $this->em->persist($school);
        }

        $this->em->flush();

        $io->note('Import szkół zakończony pomyślnie.');

        return Command::SUCCESS;
    }
}
