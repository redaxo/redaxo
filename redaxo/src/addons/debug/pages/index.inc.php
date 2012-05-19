<?php

// PARAMS
////////////////////////////////////////////////////////////////////////////////
$page       = rex_request('page', 'string');
$subpage    = rex_request('subpage', 'string');
$subsubpage = rex_request('subsubpage', 'string');
$func       = rex_request('func', 'string');

$subpage = $subpage=='' ? 'settings' : $subpage;


// PAGE OUTPUT
////////////////////////////////////////////////////////////////////////////////
echo rex_view::title($this->i18n('name'));

include $this->getBasePath('pages/'. $subpage .'.inc.php');
