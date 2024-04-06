<?php

namespace Redaxo\Core\Console\Command;

use Override;
use rex_be_style;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class AssetsCompileStylesCommand extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this->setDescription('Converts Backend SCSS files to CSS');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('be_style scss compiler');

        rex_be_style::compile();

        $io->success('Styles successfully compiled');

        return Command::SUCCESS;
    }
}
