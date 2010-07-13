<?php

/**
 * Image-Resize Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author <a href="http://www.vscope.at">www.vscope.at</a>
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'image_resize';

/* Addon Parameter */
$REX['ADDON']['rxid'][$mypage] = '469';
$REX['ADDON']['name'][$mypage] = 'Image Resize';
$REX['ADDON']['perm'][$mypage] = 'image_resize[]';
$REX['ADDON']['version'][$mypage] = '1.3';
$REX['ADDON']['author'][$mypage] = 'Markus Staab, Jan Kristinus, Wolfgang Hutteger';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['PERM'][] = 'image_resize[]';

/* User Parameter */
// $REX['ADDON']['image_resize']['default_filters'] = array('brand');
$REX['ADDON']['image_resize']['default_filters'] = array();

// --- DYN
$REX['ADDON']['image_resize']['max_cachefiles'] = 5;
$REX['ADDON']['image_resize']['max_filters'] = 5;
$REX['ADDON']['image_resize']['max_resizekb'] = 1000;
$REX['ADDON']['image_resize']['max_resizepixel'] = 500;
$REX['ADDON']['image_resize']['jpg_quality'] = 75;
// --- /DYN

include_once ($REX['INCLUDE_PATH'].'/addons/image_resize/classes/class.thumbnail.inc.php');

require_once $REX['INCLUDE_PATH'].'/addons/image_resize/extensions/extension_wysiwyg.inc.php';
rex_register_extension('OUTPUT_FILTER', 'rex_resize_wysiwyg_output');

if ($REX['REDAXO'])
{
	// Bei Update Cache loeschen
  if(!function_exists('rex_image_ep_mediaupdated'))
  {
  	rex_register_extension('MEDIA_UPDATED', 'rex_image_ep_mediaupdated');
  	function rex_image_ep_mediaupdated($params){
  		rex_thumbnail::deleteCache($params["filename"]);
  	}
  }
}

// Resize Script
$rex_resize = rex_get('rex_resize', 'string');
if ($rex_resize != '')
{
	rex_thumbnail::createFromUrl($rex_resize);
}

if($REX['REDAXO'])
{
	$I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');
	$REX['ADDON'][$mypage]['SUBPAGES'] = array (
  	array ('', $I18N->msg('iresize_subpage_desc')),
  	array ('settings', $I18N->msg('iresize_subpage_config')),
  	array ('clear_cache', $I18N->msg('iresize_subpage_clear_cache')),
	);
}