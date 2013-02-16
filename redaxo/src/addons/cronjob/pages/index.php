<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$subpage = rex_be_controller::getCurrentPagePart(2);
$func    = rex_request('func', 'string');
$oid     = rex_request('oid', 'int');

echo rex_view::title($this->i18n('title'));

echo "\n  <div class=\"rex-addon-output-v2\">\n  ";

include rex_be_controller::getCurrentPageObject()->getSubPath();

echo "\n  </div>";
