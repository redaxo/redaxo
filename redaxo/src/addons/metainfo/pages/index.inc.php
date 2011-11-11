<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// Parameter
$Basedir = dirname(__FILE__);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

rex_title('Metainformationen erweitern');

echo '<div class="rex-addon-output-v2">';

// Include Current Page
switch($subpage)
{
  case 'media' :
  {
    $prefix = 'med_';
    break;
  }
  case 'categories' :
  {
    $prefix = 'cat_';
    break;
  }
  default:
  {
	  $prefix = 'art_';
  }
}

$metaTable = rex_metainfo_meta_table($prefix);

require $Basedir .'/field.inc.php';

echo '</div>';