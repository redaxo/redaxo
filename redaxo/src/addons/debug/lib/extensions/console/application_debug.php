<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_console_application_debug extends rex_console_application {

    private $consoleData = null;

    public function getConsoleData() {
        return $this->consoleData;
    }

    protected function doRunCommand(\Symfony\Component\Console\Command\Command $command, \Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        if ($output instanceof \Symfony\Component\Console\Output\ConsoleOutput) {
            $output = new rex_console_output_debug();
        }
        $exitCode = parent::doRunCommand($command, $input, $output);
        $this->consoleData = [
            'name' => $command->getName(),
            'exitCode' => $exitCode,
            'arguments' => $input->getArguments(),
            'options' => $input->getOptions(),
            'defaultArguments' => $command->getDefinition()->getArgumentDefaults(),
            'defaultOptions' => $command->getDefinition()->getOptionDefaults(),
            'output' => $output->getOutput()
        ];

        return $exitCode;
    }
}