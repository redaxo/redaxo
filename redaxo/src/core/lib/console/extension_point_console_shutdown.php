<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @extends rex_extension_point<null> */
class rex_extension_point_console_shutdown extends rex_extension_point
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
