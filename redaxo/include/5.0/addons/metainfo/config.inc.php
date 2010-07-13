<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'metainfo';

if(!defined('REX_A62_FIELD_TEXT'))
{
  // Feldtypen
  define('REX_A62_FIELD_TEXT',                 1);
  define('REX_A62_FIELD_TEXTAREA',             2);
  define('REX_A62_FIELD_SELECT',               3);
  define('REX_A62_FIELD_RADIO',                4);
  define('REX_A62_FIELD_CHECKBOX',             5);
  define('REX_A62_FIELD_REX_MEDIA_BUTTON',     6);
  define('REX_A62_FIELD_REX_MEDIALIST_BUTTON', 7);
  define('REX_A62_FIELD_REX_LINK_BUTTON',      8);
  define('REX_A62_FIELD_REX_LINKLIST_BUTTON',  9);
  define('REX_A62_FIELD_DATE',                 10);
  define('REX_A62_FIELD_DATETIME',             11);
  define('REX_A62_FIELD_LEGEND',               12);
  define('REX_A62_FIELD_TIME',                 13);
  
  define('REX_A62_FIELD_COUNT',                13);
}

$REX['ADDON']['rxid'][$mypage] = '62';
$REX['ADDON']['name'][$mypage] = 'Meta Infos';
$REX['ADDON']['perm'][$mypage] = 'admin[]';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab, Jan Kristinus";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['prefixes'][$mypage] = array('art_', 'cat_', 'med_');
$REX['ADDON']['metaTables'][$mypage] = array(
  'art_' => $REX['TABLE_PREFIX'] .'article',
  'cat_' => $REX['TABLE_PREFIX'] .'article',
  'med_' => $REX['TABLE_PREFIX'] .'file',
);

if ($REX['REDAXO'])
{
  $I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/lang');
  
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/class.rex_input.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_text.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_textarea.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_mediabutton.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_medialistbutton.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_linkbutton.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_linklistbutton.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_date.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_time.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_datetime.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage .'/classes/input/class.rex_input_select.inc.php';
  
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/classes/class.rex_restrictions_element.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/classes/class.rex_table_manager.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/functions/function_metainfo.inc.php';
  require_once $REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/extensions/extension_common.inc.php';

  rex_register_extension('PAGE_CHECKED', 'a62_extensions_handler');

	$REX['ADDON']['pages'][$mypage] = array(
	  array('', $I18N->msg('article')),
	  array('categories', $I18N->msg('categories')),
	  array('media', $I18N->msg('media')),
	);

}