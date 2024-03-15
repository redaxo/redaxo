<?php

use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;

$successfullyCompleted = rex_setup::markSetupCompleted();

if ($successfullyCompleted) {
    $errmsg = '';
} else {
    // XXX diese meldung wird nirgends ausgegeben?
    $errmsg = I18n::msg('setup_601');
}

$headline = rex_view::title(I18n::msg('setup_600'));

$content = '<h3>' . I18n::msg('setup_603') . '</h3>';
$content .= I18n::rawMsg('setup_604', '<a href="' . Url::backendController() . '">', '</a>');
$content .= '<p>' . I18n::msg('setup_605') . '</p>';

$buttons = '<a class="btn btn-setup" href="' . Url::backendController() . '">' . I18n::msg('setup_606') . '</a>';

echo $headline;

$fragment = new rex_fragment();
$fragment->setVar('heading', I18n::msg('setup_602'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
echo $fragment->parse('core/page/section.php');
