<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class rex_extension_point_console_shutdown extends rex_extension_point
{
    public const NAME = 'CONSOLE_SHUTDOWN';

    private $command;
    private $input;
    private $output;
    private $exitCode;

    /**
     * @param int $exitCode
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, $exitCode)
    {
        $subject = null;
        $params = [];
        $readonly = true;

        parent::__construct(self::NAME, $subject, $params, $readonly);
    }

    public function getCommand():Command
    {
        return $this->command;
    }

    public function getInput():InputInterface
    {
        return $this->input;
    }

    public function getOutput():OutputInterface
    {
        return $this->output;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
