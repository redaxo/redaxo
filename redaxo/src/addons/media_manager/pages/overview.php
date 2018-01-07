<?php

/** @var rex_addon $this */

ob_start();
require __DIR__ . '/../help.php';
$content = ob_get_clean();

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::RawMsg('media_manager_overview_title', $this->getVersion()), false);
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
