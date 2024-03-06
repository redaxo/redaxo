<?php

use Redaxo\Core\Translation\I18n;

$content = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_PAGE_CONTENT', '', []));

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('be_style_themes'), false);
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
