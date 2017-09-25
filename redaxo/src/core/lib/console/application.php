<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;

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
            throw new FatalThrowableError($e);
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
        if ('ydeploy:migrate' === $command->getName()) {
            $command->getPackage()->boot();

            return;
        }

        foreach (rex::getConfig('package-order') as $packageId) {
            rex_package::get($packageId)->boot();
        }

        rex_extension::registerPoint(new rex_extension_point('PACKAGES_INCLUDED'));
    }
}
