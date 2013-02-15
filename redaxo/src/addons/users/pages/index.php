<?php

/**
 *
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');
$id = rex_request('id', 'int');

echo rex_view::title(rex_i18n::msg('user_management'));

include rex_be_controller::getCurrentPageObject()->getSubPath();
