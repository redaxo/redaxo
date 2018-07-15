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

        // Copied from be_style/boot.php
        $compiler = new rex_scss_compiler();
        $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', [$this->getPackage()->getPath('scss/master.scss')]));
        $compiler->setScssFile($scss_files);

        // Compile in backend assets dir
        $compiler->setCssFile($this->getPackage()->getPath('assets/css/styles.css'));
        $compiler->compile();

        // Compiled file to copy in frontend assets dir
        rex_file::copy($this->getPackage()->getPath('assets/css/styles.css'), $this->getPackage()->getAssetsPath('css/styles.css'));

        if ($rexPlugin->isAvailable()) {
            $io->progressAdvance();

            // Copied from be_style/redaxo/boot.php
            $compiler = new rex_scss_compiler();
            $compiler->setRootDir($rexPlugin->getPath('scss/'));
            $compiler->setScssFile($rexPlugin->getPath('scss/master.scss'));

            // Compile in backend assets dir
            $compiler->setCssFile($rexPlugin->getPath('assets/css/styles.css'));

            $compiler->compile();

            // Compiled file to copy in frontend assets dir
            rex_file::copy($rexPlugin->getPath('assets/css/styles.css'), $rexPlugin->getAssetsPath('css/styles.css'));
        }
        $io->progressFinish();

        $io->success('Styles successfully compiled');
    }
}
