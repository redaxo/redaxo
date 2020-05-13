<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class rex_console_output_debug extends ConsoleOutput
{
    private $output = '';

    protected function doWrite($message, $newline)
    {
        $this->output .= $message;
        if ($newline) {
            $this->output .= PHP_EOL;
        }
        parent::doWrite($message, $newline);
    }

    public function getOutput()
    {
        return $this->output;
    }
}
