<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @author staabm
 *
 * @internal
 */
class rex_command_setup_check extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Check the commandline interface (CLI) environment for REDAXO requirements')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        
        $errors = rex_setup::checkEnvironment();
        if (count($errors) == 0) {
            $io->success('PHP version ok');
        }
        
        $res = rex_setup::checkFilesystem();
        if (count($res) > 0) {
            $base = rex_path::base();
            foreach ($res as $key => $messages) {
                if (count($messages) > 0) {
                    $affectedFiles = [];
                    foreach ($messages as $message) {
                        $affectedFiles[] = str_replace($base, '', $message);
                    }
                    $errors[] = rex_i18n::msg($key) . "\n". implode("\n", $affectedFiles);
                }
            }
        } else {
            $io->success('Directory permissions ok');
        }

        $config = null;
        $configFile = rex_path::coreData('config.yml');
        if ($configFile) {
            $config = rex_file::getConfig($configFile);
        }
        try {
            if ($config) {
                $err = rex_setup::checkDb($config, false);
            } else {
                $err = 'config.yml not found';
            }
            if ($err) {
                $errors[] = $err;
            } else {
                $io->success('Datenbank ok');
            }
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }

        if ($errors) {
            $errors = array_map([$this, 'decodeMessage'], $errors);
            
            throw new \Exception(implode("\n", $errors));
        }                
    }
}
