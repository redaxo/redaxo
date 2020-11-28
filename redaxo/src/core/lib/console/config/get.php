<?php

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_config_get extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('Get config variables')
            ->addArgument('config-key', InputArgument::REQUIRED, 'config path separated by periods, e.g. "setup" or "db.1.host"')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'php type of the returned value, e.g. "octal"', 'string')
            ->addOption('package', 'p', InputOption::VALUE_REQUIRED, 'package to inspect, defaults to redaxo-core', 'core')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $key = $input->getArgument('config-key');
        $type = $input->getOption('type');

        if (!$key) {
            throw new InvalidArgumentException('config-key is required');
        }

        $path = explode('.', $key);

        $propertyKey = array_shift($path);

        $package = $input->getOption('package');
        if ('core' === $package) {
            $config = rex::getProperty($propertyKey);
        } else {
            $config = rex_package::get($package)->getProperty($propertyKey);
        }

        if (null === $config) {
            $io->getErrorStyle()->error('Config key not found');
            return 1;
        }
        foreach ($path as $pathPart) {
            if (!isset($config[$pathPart])) {
                $io->getErrorStyle()->error('Config key not found');
                return 1;
            }
            $config = $config[$pathPart];
        }

        if ('octal' === $type) {
            // turn fileperm/dirperm into the expected values like e.g. 755
            $output->writeln(decoct($config));
        } else {
            $output->writeln(json_encode($config));
        }

        return 0;
    }
}
