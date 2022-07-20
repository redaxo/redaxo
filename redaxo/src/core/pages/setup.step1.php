<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($cancelSetupBtn));

rex_setup::init();

$initial = rex_setup::isInitialSetup();
$current = rex_i18n::getLocale();

$langs = [];
foreach (rex_i18n::getLocales() as $locale) {
    $label = rex_i18n::msgInLocale('lang', $locale);
    $active = !$initial && $current === $locale ? ' active' : '';
    $langs[$label] = '<a class="list-group-item'.$active.'" href="' . $context->getUrl(['lang' => $locale, 'step' => 2]) . '">' . $label . '</a>';
}
ksort($langs);
echo rex_view::title(rex_i18n::msg('setup_100').$cancelSetupBtn);
$content = '<div class="list-group">' . implode('', $langs) . '</div>';

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('setup_101'), false);
$fragment->setVar('content', $content, false);

if (!$initial) {
    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['lang' => $current, 'step' => 2]) . '">' . rex_i18n::msg('setup_110') . '</a>';

    $fragment->setVar('buttons', $buttons, false);
}

echo $fragment->parse('core/page/section.php');
