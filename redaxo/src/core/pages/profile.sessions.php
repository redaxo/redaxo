<?php

$list = rex_list::factory('Select session_id, ip, useragent, starttime, last_activity from rex_user_session where user_id = '.rex::requireUser()->getId());
$list->setColumnFormat('last_activity', 'custom', static function () use ($list) {
    if (session_id() === $list->getValue('session_id')) {
        return 'active session';
    }
    return $list->getValue('last_activity');
});
$content = $list->get();

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('user_caption'));
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
