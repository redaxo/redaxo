<?php

$successfullyCompleted = rex_setup::markSetupCompleted();

if ($successfullyCompleted) {
    $errmsg = '';
} else {
    // XXX diese meldung wird nirgends ausgegeben?
    $errmsg = rex_i18n::msg('setup_601');
}

$headline = rex_view::title(rex_i18n::msg('setup_600'));

$content = '<h3>' . rex_i18n::msg('setup_603') . '</h3>';
$content .= rex_i18n::rawMsg('setup_604', '<a href="' . rex_url::backendController() . '">', '</a>');
$content .= '<p>' . rex_i18n::msg('setup_605') . '</p>';

$buttons = '<a class="btn btn-setup" href="' . rex_url::backendController() . '">' . rex_i18n::msg('setup_606') . '</a>';

echo $headline;

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('setup_602'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
echo $fragment->parse('core/page/section.php');
