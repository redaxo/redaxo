<?php

// Todo:
// BUG: Editierseite unterhalb der Versionaktualisierung updaten ..
// an/aus
// cronjob mit bereinigung von unnötigen versionen

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('history_title_info'));
$fragment->setVar('body', rex_i18n::rawMsg('history_info_content'), false);
echo $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('history_todos'));
$fragment->setVar('body', rex_i18n::rawMsg('history_todos_content', true), false);
echo $fragment->parse('core/page/section.php');

?>