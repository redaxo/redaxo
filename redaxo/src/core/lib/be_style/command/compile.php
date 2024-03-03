<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class rex_command_be_style_compile extends rex_console_command
{
    protected function configure(): void
    {
        $this->setAliases(['styles:compile'])
            ->setDescription('Converts Backend SCSS files to CSS');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('be_style scss compiler');

        rex_be_style::compile();

        $io->success('Styles successfully compiled');

        return 0;
    }
}
