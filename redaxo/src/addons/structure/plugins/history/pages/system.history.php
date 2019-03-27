<?php

if (rex_request('func', 'string') == 'clearall') {
    rex_article_slice_history::clearAllHistory();
    echo rex_view::success($this->i18n('deleted'));
}

$content = rex_i18n::rawMsg('structure_history_info_content');
$content .= '<p><a href="' . rex_url::currentBackendPage(['func' => 'clearall']). '" class="btn btn-setup">' . $this->i18n('button_delete_history') . '</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('title_info'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('todos'));
$fragment->setVar('body', rex_i18n::rawMsg('structure_history_todos_content', true), false);
echo $fragment->parse('core/page/section.php');
