<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\be_style
 *
 * @author bloep
 *
 * @internal
 */
class rex_be_style_command_compile extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('Converts Backend SCSS files to CSS');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('be_style scss compiler');

        // If the plugin redaxo is enabled, compile these styles as well
        $rexPlugin = rex_plugin::get($this->package->getName(), 'redaxo');
        $io->progressStart($rexPlugin->isAvailable() ? 2 : 1);

        require_once $this->package->getPath('functions/rex_be_style_compile.php');
        rex_be_style_compile();

        if ($rexPlugin->isAvailable()) {
            $io->progressAdvance();

            require_once $rexPlugin->getPath('functions/rex_be_style_redaxo_compile.php');
            rex_be_style_redaxo_compile();
        }
        $io->progressFinish();

        $io->success('Styles successfully compiled');
    }
}
