<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(1);
$func    = rex_request('func', 'string');
$oid     = rex_request('oid', 'int');

echo rex_view::title($this->i18n('title'));

echo "\n  <div class=\"rex-addon-output-v2\">\n  ";

if (!in_array($subpage, array('log')))
  $subpage = 'cronjobs';

require $this->getBasePath('pages/' . $subpage . '.inc.php');

echo "\n  </div>";
