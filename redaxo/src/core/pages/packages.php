<?php

/**
 * @package redaxo5
 */

$subpage = rex_request('subpage', 'string');

if ('changelog' == $subpage) {
    require __DIR__ .'/packages.changelog.php';
}

if ('help' == $subpage) {
    require __DIR__ .'/packages.help.php';
}

if ('license' == $subpage) {
    require __DIR__ .'/packages.license.php';
}

if ('' == $subpage) {
    require __DIR__ .'/packages.list.php';
}
