<?php

use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Translation\I18n;

$content = Extension::registerPoint(new ExtensionPoint('BE_STYLE_PAGE_CONTENT', '', []));

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('be_style_themes'), false);
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
