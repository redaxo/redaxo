<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 */
class rex_console_application extends Application
{
    public function __construct()
    {
        parent::__construct('REDAXO', rex::getVersion());
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        try {
            return parent::doRun($input, $output);
        } catch (\Exception $e) {
            // catch and rethrow \Exceptions first to only catch fatal errors below (\Exception implements \Throwable)
            throw $e;
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            if ($e instanceof \ParseError) {
                $message = 'Parse error: '.$message;
                $severity = E_PARSE;
            } elseif ($e instanceof \TypeError) {
                $message = 'Type error: '.$message;
                $severity = E_RECOVERABLE_ERROR;
            } else {
                $message = 'Fatal error: '.$message;
                $severity = E_ERROR;
            }

            throw new ErrorException($message, $e->getCode(), $severity, $e->getFile(), $e->getLine(), $e->getPrevious());
        }
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof rex_console_command) {
            $this->loadPackages($command);
        }

        return parent::doRunCommand($command, $input, $output);
    }

    private function loadPackages(rex_console_command $command)
    {
        // Some packages requires a working db connection in their boot.php
        // in this case if no connection is available, no commands can be used
        // but this command should be always usable
        if (in_array($command->getName(), ['db:set-connection', 'setup:run'], true)) {
            return;
        }

        if ('ydeploy:migrate' === $command->getName()) {
            // boot only the ydeploy package, which provides the migrate command
            $command->getPackage()->boot();

            return;
        }

        if (!rex::isSetup()) {
            // boot all known packages in the defined order
            // which reflects dependencies before consumers
            foreach (rex::getConfig('package-order') as $packageId) {
                rex_package::get($packageId)->boot();
            }
        }

        rex_extension::registerPoint(new rex_extension_point('PACKAGES_INCLUDED'));
    }
}
