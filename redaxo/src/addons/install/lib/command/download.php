<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Console\Command\AbstractCommand;
use Redaxo\Core\Util\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class rex_command_install_download extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this->setDescription('Download an AddOn from redaxo.org')
            ->addArgument('addonkey', InputArgument::REQUIRED, 'AddOn key, e.g. "yform"')
            ->addArgument('version', InputArgument::OPTIONAL, "Version, e.g. '3.2.1', '^3.2' or '3.*'");
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $addonKey = $input->getArgument('addonkey');

        if (Addon::exists($addonKey)) {
            $io->error(sprintf('AddOn "%s" already exists!', $addonKey));
            return 1;
        }

        $packages = rex_install_packages::getAddPackages();
        if (!isset($packages[$addonKey])) {
            $io->error(sprintf('AddOn "%s" does not exist!', $addonKey));
            return 1;
        }
        $package = $packages[$addonKey];
        $files = $package['files'];

        $version = $input->getArgument('version');

        if (null === $version) {
            $versions = [];
            foreach ($files as $fileMeta) {
                $versions[] = $fileMeta['version'];
            }

            $version = (string) $io->choice('Please choose a version', $versions);
        }

        // search fileId by version
        $fileId = null;
        $latestVersion = null;
        foreach ($files as $fId => $fileMeta) {
            if (!Version::matchesConstraints($fileMeta['version'], $version)) {
                continue;
            }

            if (null !== $latestVersion
                && !Version::compare($fileMeta['version'], $latestVersion, '>')) {
                continue;
            }

            $latestVersion = $fileMeta['version'];
            $fileId = $fId;
        }

        if (null !== $latestVersion) {
            $version = $latestVersion;
        }

        if (!$fileId || !isset($files[$fileId])) {
            $io->error(sprintf('Version "%s" not found!', $version));
            return 1;
        }

        $install = new rex_install_package_add();
        try {
            $message = $install->run($addonKey, $fileId);
        } catch (rex_exception $exception) {
            $io->error($this->decodeMessage($exception->getMessage()));
            return 1;
        }

        if ('' !== $message) {
            $io->error($this->decodeMessage($message));
            return 1;
        }

        $io->success(sprintf('AddOn "%s" with version "%s" successfully downloaded.', $addonKey, $version));
        return 0;
    }
}
