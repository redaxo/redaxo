<?php

/**
 * Page Content Modules Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

if ($error != '')
  $REX['ADDON']['installmsg']['page_content_modules'] = $error;
else
  $REX['ADDON']['install']['page_content_modules'] = true;