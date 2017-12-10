<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\core
 */
class rex_command_package_deactivate extends rex_command_package
{
    protected function configureCommand()
    {
        $this->setDescription('Deactivates the selected package');
    }

    protected function executeCommand(rex_package $package, SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $manager = rex_package_manager::factory($package);
        $success = $manager->deactivate();
        $message = $manager->getMessage();

        return ['success' => $success, 'message' => $message];
    }
}
