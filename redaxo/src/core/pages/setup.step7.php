<?php

$successfullyCompleted = rex_setup::markSetupCompleted();

if ($successfullyCompleted) {
    $errmsg = '';
} else {
    // XXX diese meldung wird nirgends ausgegeben?
    $errmsg = rex_i18n::msg('setup_701');
}

$headline = rex_view::title(rex_i18n::msg('setup_700'));

$content = '<h3>' . rex_i18n::msg('setup_703') . '</h3>';
$content .= rex_i18n::rawMsg('setup_704', '<a href="' . rex_url::backendController() . '">', '</a>');
$content .= '<p>' . rex_i18n::msg('setup_705') . '</p>';

$buttons = '<a class="btn btn-setup" href="' . rex_url::backendController() . '">' . rex_i18n::msg('setup_706') . '</a>';

echo $headline;

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('setup_702'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
echo $fragment->parse('core/page/section.php');
