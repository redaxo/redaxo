<?php

$REX['ADDON']['name']['old'] = 'Old';

$REX['ADDON']['old']['SUBPAGES'] = array(array('', 'Page 1'), array('page2', 'Page 2'));

$REX['PERM'][] = 'old[]';
$REX['EXTPERM'][] = 'old[option]';

$OLD_I18N = new i18n(rex::getProperty('lang'), $REX['INCLUDE_PATH'] . '/addons/old/lang/');