<?php

rex_setup::init();

$langs = [];
foreach (rex_i18n::getLocales() as $locale) {
    $label = rex_i18n::msgInLocale('lang', $locale);
    $langs[$label] = '<a class="list-group-item" href="' . rex_url::backendPage('setup', ['step' => 2, 'lang' => $locale]) . '">' . $label . '</a>';
}
ksort($langs);
echo rex_view::title(rex_i18n::msg('setup_100'));
$content = '<div class="list-group">' . implode('', $langs) . '</div>';

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('setup_101'), false);
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
