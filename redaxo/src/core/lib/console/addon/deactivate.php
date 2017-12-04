<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 */
class rex_command_addon_deactivate extends rex_console_command
{
    protected function configure()
    {
        $this->setName('addon:deactivate')
            ->setDescription(rex_i18n::msg('addon_deactivate'))
            ->addArgument('addon', InputArgument::REQUIRED, rex_i18n::msg('addon').' '.rex_i18n::msg('package_hname'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $packageId = $input->getArgument('addon');
        $package = rex_package::get($packageId);
        if ($package instanceof rex_null_package) {
            $io->error(rex_i18n::rawMsg('addon_not_exists', $packageId));
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
