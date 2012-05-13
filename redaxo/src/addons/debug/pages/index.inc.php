<?php

// GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$page       = rex_request('page', 'string');
$subpage    = rex_request('subpage', 'string');
$subsubpage = rex_request('subsubpage', 'string');
$func       = rex_request('func', 'string');

#FB::log($this,__CLASS__.'::'.__FUNCTION__.' $this');
echo rex_view::title($this->i18n('name'));

$subpage = $subpage=='' ? 'settings' : $subpage;

include $this->getBasePath('pages/'.$subpage.'.inc.php');