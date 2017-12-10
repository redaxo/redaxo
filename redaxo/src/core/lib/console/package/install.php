<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\core
 */
class rex_command_package_install extends rex_command_package
{
    protected function configureCommand()
    {
        $this
            ->setDescription('Installs the selected package')
            ->addOption('re-install', '-r', InputOption::VALUE_NONE, 'Allows to reinstall the Package without asking the User');
    }

    protected function executeCommand(rex_package $package, SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        if ($package->isInstalled() && !$input->getOption('re-install')) {
            $helper = $this->getHelper('question');
            $question = new \Symfony\Component\Console\Question\ConfirmationQuestion('Package "'.$package->getPackageId().'" is already installed. Should it be reinstalled? (y/n) ', false);
            if (!$helper->ask($input, $output, $question)) {
                $io->success('Package "'.$package->getPackageId().'" wasn\'t reinstalled');
                exit(0);
            }
        }

        $manager = rex_package_manager::factory($package);
        try {
            $success = $manager->install();
        } catch (rex_functional_exception $e) {
            $io->error($e->getMessage());
            exit(1);
        }
        $message = $manager->getMessage();
        return ['success' => $success, 'message' => $message];
    }
}
