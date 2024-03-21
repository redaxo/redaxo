<?php

namespace Redaxo\Core\Console\Command;

use rex_be_style;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class AssetsCompileStylesCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setDescription('Converts Backend SCSS files to CSS');
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
