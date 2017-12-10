<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 */
class rex_command_package_deactivate extends rex_console_command
{
    protected function configure()
    {
        $this->setName('package:deactivate')
            ->setDescription('Deactivates the selected package')
            ->addArgument('package-id', InputArgument::REQUIRED, 'The id of the package(addon or plugin); e.g. "cronjob" or "structure/content"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $packageId = $input->getArgument('package-id');
        $package = rex_package::get($packageId);
        if ($package instanceof rex_null_package) {
            $io->error('Package "'.$packageId.'" doesn\'t exists!');
            return;
        }

        $manager = rex_package_manager::factory($package);
        $success = $manager->deactivate();
        $message = strip_tags($manager->getMessage());
        if ($success) {
            $io->success($message);
        } else {
            $io->error($message);
        }
    }
}
