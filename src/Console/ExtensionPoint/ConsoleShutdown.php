<?php

namespace Redaxo\Core\Console\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @extends ExtensionPoint<null> */
class ConsoleShutdown extends ExtensionPoint
{
    public const NAME = 'CONSOLE_SHUTDOWN';

    public function __construct(
        private Command $command,
        private InputInterface $input,
        private OutputInterface $output,
        private int $exitCode,
    ) {
        parent::__construct(self::NAME, null, [], true);
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
