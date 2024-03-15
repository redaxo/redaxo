<?php

use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Url;

if ('clearall' == rex_request('func', 'string')) {
    rex_article_slice_history::clearAllHistory();
    echo rex_view::success(I18n::msg('structure_history_deleted'));
}

$content = I18n::rawMsg('structure_history_info_content');
$content .= '<p><a href="' . Url::currentBackendPage(['func' => 'clearall']) . '" class="btn btn-setup">' . I18n::msg('structure_history_button_delete_history') . '</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('structure_history_title_info'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('structure_history_todos'));
$fragment->setVar('body', I18n::rawMsg('structure_history_todos_content'), false);
echo $fragment->parse('core/page/section.php');
