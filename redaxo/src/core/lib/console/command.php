<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\core
 */
abstract class rex_console_command extends Command
{
    /** @var rex_package */
    protected $package;

    public function setPackage(rex_package $package)
    {
        $this->package = $package;

        return $this;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function requiresOtherPackages()
    {
        return true;
    }

    protected function getStyle(InputInterface $input, OutputInterface $output)
    {
        return new SymfonyStyle($input, $output);
    }
}
