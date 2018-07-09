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

        $compiler = new rex_scss_compiler();
        $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', [$this->getPackage()->getPath('scss/master.scss')]));
        $compiler->setScssFile($scss_files);

        // Compile in backend assets dir
        $compiler->setCssFile($this->getPackage()->getPath('assets/css/styles.css'));
        $compiler->compile();

        // Compiled file to copy in frontend assets dir
        rex_file::copy($this->getPackage()->getPath('assets/css/styles.css'), $this->getPackage()->getAssetsPath('css/styles.css'));

        $io->success('Styles successfully compiled');
    }
}
