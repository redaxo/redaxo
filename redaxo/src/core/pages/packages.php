<?php

$subpage = rex_request('subpage', 'string');

if ('' == $subpage) {
    require __DIR__ .'/packages.list.php';
} else {
    require __DIR__ .'/packages.details.php';
}
