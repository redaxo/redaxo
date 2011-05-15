<?php

/**
 * RSS Reader Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'rss_reader';

// im backend und eingeloggt?
if(rex_core::isBackend() && rex_core::getUser())
{
  if(rex_request('page', 'string') == 'be_dashboard')
  {
    require_once dirname(__FILE__) .'/functions/function_reader.inc.php';

    rex_extension::register(
      'DASHBOARD_COMPONENT',
      array(new rex_rss_reader_component(), 'registerAsExtension')
    );
  }
}
