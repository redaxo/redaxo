<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\core
 */
abstract class rex_command_package extends rex_console_command
{
    protected function configure()
    {
        $this->addArgument('package-id', InputArgument::REQUIRED, 'The id of the package (addon or plugin); e.g. "cronjob" or "structure/content"');
        $this->configureCommand();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $packageId = $input->getArgument('package-id');
        $package = rex_package::get($packageId);
        if ($package instanceof rex_null_package) {
            $io->error('Package "'.$packageId.'" doesn\'t exists!');
            exit(1);
        }

        $result = $this->executeCommand($package, $io, $input, $output);

        $message = strip_tags($result['message']);
        if ($result['success']) {
            $io->success($message);
            exit(0);
        }
        $io->error($message);
        exit(1);
    }

    abstract protected function configureCommand();

    /**
     * @param rex_package     $package
     * @param SymfonyStyle    $io
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array Returns an array, whether the command was successful and the status message
     */
    abstract protected function executeCommand(rex_package $package, SymfonyStyle $io, InputInterface $input, OutputInterface $output);
}
