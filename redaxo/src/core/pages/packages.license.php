<?php

$package = rex_package::get(rex_request('package', 'string'));

$license = null;
if (is_readable($package->getPath('LICENSE.md'))) {
    $license = rex_markdown::factory()->parse(rex_file::require($package->getPath('LICENSE.md')));
} elseif (is_readable($package->getPath('LICENSE'))) {
    $license = nl2br(file_get_contents($package->getPath('LICENSE')));
}

if ($license) {
    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('credits_license'));
    $fragment->setVar('body', $license, false);
    echo '<div id="license"></div>'; // scroll anchor
    echo $fragment->parse('core/page/section.php');
}

echo '<a class="btn btn-back" href="javascript:history.back();">' . rex_i18n::msg('package_back') . '</a>';
echo '<a class="btn" rel="noopener noreferrer" target="_blank" href="https://choosealicense.com/licenses/">' . rex_i18n::msg('credits_explain_license') . ' <i class="fa fa-external-link"></i></a>';
