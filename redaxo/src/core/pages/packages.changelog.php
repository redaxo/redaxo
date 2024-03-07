<?php

use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Translation\I18n;

$content = '';

$package = rex_addon::get(rex_request('package', 'string'));

if (is_readable($package->getPath('CHANGELOG.md'))) {
    [$readmeToc, $readmeContent] = rex_markdown::factory()->parseWithToc(File::require($package->getPath('CHANGELOG.md')), 1, 2, [
        rex_markdown::SOFT_LINE_BREAKS => false,
        rex_markdown::HIGHLIGHT_PHP => true,
    ]);
    $fragment = new rex_fragment();
    $fragment->setVar('content', $readmeContent, false);
    $fragment->setVar('toc', $readmeToc, false);
    $content .= $fragment->parse('core/page/docs.php');
}

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('credits_changelog'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

echo '<p><a class="btn btn-back" href="' . rex_url::backendPage('packages') . '">' . I18n::msg('package_back') . '</a></p>';
