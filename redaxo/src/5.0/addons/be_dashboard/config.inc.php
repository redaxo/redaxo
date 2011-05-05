<?php

/**
 * Backenddashboard Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 * 
 * @package redaxo5
 * @version svn:$Id$
 */

$REX['PERM'][] = 'be_dashboard[]';

// im backend und eingeloggt?
if($REX["REDAXO"] && $REX["USER"])
{
  if(rex_request('page', 'string') == 'be_dashboard')
  {
    require_once dirname(__FILE__) .'/functions/function_dashboard.inc.php';
    rex_extension::register('PAGE_HEADER', 'rex_a655_add_assets');
  }
}
