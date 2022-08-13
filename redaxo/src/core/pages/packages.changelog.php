<?php

$content = '';

$package = rex_package::get(rex_request('package', 'string'));

if (is_readable($package->getPath('CHANGELOG.md'))) {
    [$readmeToc, $readmeContent] = rex_markdown::factory()->parseWithToc(rex_file::require($package->getPath('CHANGELOG.md')), 1, 2, [
        rex_markdown::SOFT_LINE_BREAKS => false,
        rex_markdown::HIGHLIGHT_PHP => true,
    ]);
    $fragment = new rex_fragment();
    $fragment->setVar('content', $readmeContent, false);
    $fragment->setVar('toc', $readmeToc, false);
    $content .= $fragment->parse('core/page/docs.php');
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('credits_changelog'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

echo '<p><a class="btn btn-back" href="'.rex_url::backendPage('packages').'">' . rex_i18n::msg('package_back') . '</a></p>';
