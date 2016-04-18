<?php

if (rex_request("func","string") == "clearall") {
    rex_article_slice_history::clearAllHistory();
    echo rex_view::success(rex_i18n::msg('history_deleted'));
}

$content = rex_i18n::rawMsg('history_info_content');
$content .= '<p><a href="index.php?page=system/history&func=clearall" class="btn btn-setup">'.rex_i18n::msg('history_button_delete_history').'</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('history_title_info'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('history_todos'));
$fragment->setVar('body', rex_i18n::rawMsg('history_todos_content', true), false);
echo $fragment->parse('core/page/section.php');

?>