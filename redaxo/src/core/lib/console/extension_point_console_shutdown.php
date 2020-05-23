<?php

class rex_extension_point_console_shutdown extends rex_extension_point {
    public const NAME = 'CONSOLE_SHUTDOWN';

    private $command;
    private $input;
    private $output;
    private $exitCode;

    public function __construct(Symfony\Component\Console\Command\Command $command, \Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output, $exitCode)
    {
        $subject = null;
        $params = [];
        $readonly = true;

        parent::__construct(self::NAME, $subject, $params, $readonly);
    }

    public function getCommand() {
        return $this->command;
    }

    public function getInput() {
        return $this->input;
    }

    public function getOutput() {
        return $this->output;
    }

    public function getExitCode():int {
        return $this->exitCode;
    }
}
