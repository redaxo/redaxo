<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_config_set extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('Set config variables')
            ->addArgument('config-key', InputArgument::REQUIRED, 'config path separated by periods, e.g. "setup" or "db.1.host"')
            ->addArgument('value', InputArgument::REQUIRED, 'new value for config key, e.g. "somestring" or "1"')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'php type of new value, e.g. "bool" or "int"', 'string');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $key = $input->getArgument('config-key');
        $value = $input->getArgument('value');

        $path = explode('.', $key);

        $configFile = rex_path::coreData('config.yml');
        $baseConfig = rex_file::getConfig($configFile);
        $config = &$baseConfig;

        foreach ($path as $i => $pathPart) {
            if (!isset($config[$pathPart]) || !is_array($config[$pathPart])) {
                $config[$pathPart] = [];
            }
            if ($i === count($path) - 1) {
                $config[$pathPart] = rex_type::cast($value, $input->getOption('type'));
                break;
            }
            $config = &$config[$pathPart];
        }

        if (rex_file::putConfig($configFile, $baseConfig)) {
            $io->success('Config variable successfully saved.');
            return 0;
        }

        $io->error('Config variable couldn\'t be saved.');
        return 1;
    }
}
