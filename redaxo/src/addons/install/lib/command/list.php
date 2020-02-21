<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo/core
 *
 * @internal
 */
class rex_command_install_list extends rex_console_command {
    protected function configure()
    {
        $this->setDescription('Lists available packages on redaxo.org')
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'filter list')
            ->addOption('json', null, InputOption::VALUE_NONE, 'output table as json')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $search = $input->getOption('search');

        $packages = [];
        foreach(rex_install_packages::getAddPackages() as $key => $package) {
            if (null !== $search && stripos($key, $search) === false) {
                continue;
            }
            $packages[] = [
                'key' => $key,
                'name' => $package['name'],
                'author' => $package['author'],
                'last updated' => rex_formatter::strftime($package['updated']),
                'latest version' => reset($package['files'])['version']
            ];
        }

        if (false !== $input->getOption('json')) {
            $io->writeln(json_encode($packages));
            return 0;
        }

        $io->table(['key', 'name', 'author', 'last updated', 'latest version'], $packages);
    }
}
