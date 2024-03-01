<?php

if ('clearall' == rex_request('func', 'string')) {
    rex_article_slice_history::clearAllHistory();
    echo rex_view::success(rex_i18n::msg('structure_history_deleted'));
}

$content = rex_i18n::rawMsg('structure_history_info_content');
$content .= '<p><a href="' . rex_url::currentBackendPage(['func' => 'clearall']) . '" class="btn btn-setup">' . rex_i18n::msg('structure_history_button_delete_history') . '</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('structure_history_title_info'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('structure_history_todos'));
$fragment->setVar('body', rex_i18n::rawMsg('structure_history_todos_content'), false);
echo $fragment->parse('core/page/section.php');
