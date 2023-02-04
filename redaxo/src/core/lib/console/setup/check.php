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
    protected function configure(): void
    {
        $this
            ->setDescription('Check the commandline interface (CLI) environment for REDAXO requirements')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;
        $io = $this->getStyle($input, $output);

        $errors = rex_setup::checkEnvironment();
        if (0 == count($errors)) {
            $io->success('PHP version ok');
        } else {
            $exitCode = 1;
            $errors = array_map($this->decodeMessage(...), $errors);
            $io->error("PHP version errors:\n" .implode("\n", $errors));
        }

        $res = rex_setup::checkFilesystem();
        if (count($res) > 0) {
            $errors = [];
            foreach ($res as $key => $messages) {
                if (count($messages) > 0) {
                    $affectedFiles = [];
                    foreach ($messages as $message) {
                        $affectedFiles[] = rex_path::relative($message);
                    }
                    $errors[] = rex_i18n::msg($key) . "\n". implode("\n", $affectedFiles);
                }
            }

            $exitCode = 2;
            $errors = array_map($this->decodeMessage(...), $errors);
            $io->error("Directory permissions error:\n" .implode("\n", $errors));
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
                $exitCode = 3;
                $io->error("Database error:\n". $this->decodeMessage($err));
            } else {
                $io->success('Database ok');
            }
        } catch (PDOException $e) {
            $exitCode = 3;
            $io->error("Database error:\n". $e->getMessage());
        }

        return $exitCode;
    }
}
