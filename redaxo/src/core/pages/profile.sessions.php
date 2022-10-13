<?php

$list = rex_list::factory('Select session_id, ip, useragent, starttime, last_activity from rex_user_session where user_id = '.rex::requireUser()->getId());

$list->setColumnLabel('session_id', rex_i18n::msg('session_id'));
$list->setColumnLabel('ip', rex_i18n::msg('ip'));
$list->setColumnLabel('useragent', rex_i18n::msg('user_agent'));
$list->setColumnLabel('starttime', rex_i18n::msg('starttime'));
$list->setColumnLabel('last_activity', rex_i18n::msg('last_activity'));

$list->setColumnFormat('last_activity', 'custom', static function () use ($list) {
    if (session_id() === $list->getValue('session_id')) {
        return rex_i18n::msg('active_session');
    }
    return rex_formatter::date((string) $list->getValue('last_activity'), 'd.m.Y H:i');
});
$list->setColumnFormat('starttime', 'custom', static function () use ($list) {
    return rex_formatter::date((string) $list->getValue('starttime'), 'd.m.Y H:i');
});
$content = $list->get();

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('session_caption'));
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
