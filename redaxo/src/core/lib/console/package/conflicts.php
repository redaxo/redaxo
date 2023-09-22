<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class rex_command_package_conflicts extends rex_console_command {
    protected function configure(): void
    {
        $this->setDescription('Scan for composer package conflicts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $composerPath = rex_path::findBinaryPath('composer');
        if (!$composerPath) {
            $io->error('unable to find composer');
            return 1;
        }

        $result = (new rex_package_conflicts())->getComposerConflicts();

        if ($result) {
            $io->error('package conflicts detected');
            $io->write($result);
            return 1;
        }

        $io->success('no conflicts detected');

        return 0;
    }
}
