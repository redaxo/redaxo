<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$mypage = 'metainfo';

if (!defined('REX_METAINFO_FIELD_TEXT')) {
  // Feldtypen
  define('REX_METAINFO_FIELD_TEXT',                 1);
  define('REX_METAINFO_FIELD_TEXTAREA',             2);
  define('REX_METAINFO_FIELD_SELECT',               3);
  define('REX_METAINFO_FIELD_RADIO',                4);
  define('REX_METAINFO_FIELD_CHECKBOX',             5);
  define('REX_METAINFO_FIELD_REX_MEDIA_BUTTON',     6);
  define('REX_METAINFO_FIELD_REX_MEDIALIST_BUTTON', 7);
  define('REX_METAINFO_FIELD_REX_LINK_BUTTON',      8);
  define('REX_METAINFO_FIELD_REX_LINKLIST_BUTTON',  9);
  define('REX_METAINFO_FIELD_DATE',                 10);
  define('REX_METAINFO_FIELD_DATETIME',             11);
  define('REX_METAINFO_FIELD_LEGEND',               12);
  define('REX_METAINFO_FIELD_TIME',                 13);
  define('REX_METAINFO_FIELD_COUNT',                13);
}

$this->setProperty('prefixes', array('art_', 'cat_', 'med_'));
$this->setProperty('metaTables', array(
  'art_' => rex::getTablePrefix() . 'article',
  'cat_' => rex::getTablePrefix() . 'article',
  'med_' => rex::getTablePrefix() . 'media',
));

if (rex::isBackend()) {
  $curDir = __DIR__;
  require_once $curDir . '/functions/function_metainfo.php';

  rex_extension::register('PAGE_CHECKED', 'rex_metainfo_extensions_handler');
}
