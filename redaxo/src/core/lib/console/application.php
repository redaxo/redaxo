<?php

use Symfony\Component\Console\Application;
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
            throw $e;
        } catch (\Throwable $e) {
            throw new FatalThrowableError($e);
        }
    }
}
