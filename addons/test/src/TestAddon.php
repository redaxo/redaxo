<?php

namespace Redaxo\Test;

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Backend\Page;
use Redaxo\Core\Cronjob\AsCronjob;
use Redaxo\Core\ExtensionPoint\AsExtension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

final class TestAddon extends Addon
{
    /**
     * Adds the README as a markdown page below the system pages, so that the backend rendering of markdown
     * stays covered by the visual tests.
     *
     * @param ExtensionPoint<array<string, Page>> $ep
     */
    #[AsExtension('PAGES_PREPARED')]
    public function addMarkdownPage(ExtensionPoint $ep): void
    {
        // the system page is absent e.g. during setup
        $system = $ep->subject['system'] ?? null;

        $system?->addSubpage(new Page('markdown', 'Markdown')->setSubPath($this->getPath('README.md')));
    }

    /** Covers non-static cronjob methods, which are called on the existing addon instance. */
    #[AsCronjob('test_addon', '@hourly')]
    public function cronjob(): string
    {
        return 'Called on addon ' . $this->name;
    }
}
