<?php

use Symfony\Component\Console\Exception\InvalidArgumentException;
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
            ->addArgument('config-key', InputOption::VALUE_REQUIRED, 'config path separated by periods, e.g. "setup" or "db.1.host"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $key = $input->getArgument('config-key');

        if (!$key) {
            throw new InvalidArgumentException('config-key is required');
        }

        $path = explode('.', $key);

        $propertyKey = array_shift($path);
        $config = rex::getProperty($propertyKey);
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

        $output->writeln(json_encode($config));

        return 0;
    }
}
