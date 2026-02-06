<?php

namespace App\School\UI\Console;

use App\School\Application\MatchSchoolService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:match-school',
    description: 'Dopasowuje nazwę szkoły do rekordu w bazie'
)]
class MatchSchoolCommand extends Command
{
    public function __construct(
        private MatchSchoolService $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Nazwa szkoły wpisana przez użytkownika');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $io->text("Szukam dopasowania dla: <info>$name</info>");

        $school = $this->service->match($name);

        if (!$school) {
            $io->warning('Nie znaleziono dopasowania.');
            return Command::SUCCESS;
        }

        $io->success('Znaleziono dopasowanie:');
        $io->listing([
            'ID: ' . $school->getId(),
            'Nazwa: ' . $school->getOfficialName(),
            'Miasto: ' . $school->getCity(),
            'Typ: ' . $school->getType(),
        ]);

        return Command::SUCCESS;
    }
}
