<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\be-style
 *
 * @author bloep
 *
 * @internal
 */
class rex_be_style_command_compile extends rex_console_command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setAliases(['styles:compile'])
            ->setDescription('Converts Backend SCSS files to CSS');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('be_style scss compiler');

        rex_be_style::compile();

        $io->success('Styles successfully compiled');
    }
}
