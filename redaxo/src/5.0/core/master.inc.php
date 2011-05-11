<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- VERSION

$REX['VERSION'] = "5";
$REX['SUBVERSION'] = "0";
$REX['MINORVERSION'] = "0";

// Is set first time SQL Object ist initialised
$REX['MYSQL_VERSION'] = "";

// ----------------- default values
if (!isset($REX['NOFUNCTIONS'])) $REX['NOFUNCTIONS'] = false;

// ----------------- INCLUDE FUNCTIONS
require_once rex_path::core('functions.inc.php');

$config = rex_file::getConfig(rex_path::backend('src/config.yml'));
foreach($config as $key => $value)
{
  $REX[strtoupper($key)] = $value;
}

$REX['FILEPERM'] = octdec($REX['FILEPERM']);
$REX['DIRPERM'] = octdec($REX['DIRPERM']);

date_default_timezone_set($REX['TIMEZONE']);

// ----------------- OTHER STUFF
$REX['SETUP_PACKAGES'] = array('be_style', 'be_style/agk_skin');
$REX['SYSTEM_PACKAGES'] = array('structure', 'structure/content', 'structure/linkmap', 'modules', 'templates', 'mediapool', 'import_export', 'metainfo', 'be_search', 'be_style', 'be_style/agk_skin', 'image_manager', 'users');
$REX['MEDIAPOOL']['BLOCKED_EXTENSIONS'] = array('.php','.php3','.php4','.php5','.php6','.phtml','.pl','.asp','.aspx','.cfm','.jsp');

// ----------------- Accesskeys
$REX['ACKEY']['SAVE'] = 's';
$REX['ACKEY']['APPLY'] = 'x';
$REX['ACKEY']['DELETE'] = 'd';
$REX['ACKEY']['ADD'] = 'a';
// Wenn 2 Add Aktionen auf einer Seite sind (z.b. Struktur)
$REX['ACKEY']['ADD_2'] = 'y';
$REX['ACKEY']['LOGOUT'] = 'l';

// ------ Accesskeys for Addons
// $REX['ACKEY']['ADDON']['metainfo'] = 'm';

// ----------------- REX PERMS

// ----- allgemein
$REX['PERM'] = array();

// ----- optionen
$REX['EXTPERM'] = array();
$REX['EXTPERM'][] = 'advancedMode[]';
$REX['EXTPERM'][] = 'accesskeys[]';
$REX['EXTPERM'][] = 'moveSlice[]';
$REX['EXTPERM'][] = 'moveArticle[]';
$REX['EXTPERM'][] = 'moveCategory[]';
$REX['EXTPERM'][] = 'copyArticle[]';
$REX['EXTPERM'][] = 'copyContent[]';
$REX['EXTPERM'][] = 'publishArticle[]';
$REX['EXTPERM'][] = 'publishCategory[]';
$REX['EXTPERM'][] = 'article2startpage[]';
$REX['EXTPERM'][] = 'article2category[]';

// ----- extras
$REX['EXTRAPERM'] = array();
$REX['EXTRAPERM'][] = 'editContentOnly[]';

// ----- SET CLANG
$REX['CLANG'] = array();
$clangFile = rex_path::cache('clang.cache');
if(file_exists($clangFile))
  $REX['CLANG'] = rex_file::getCache(rex_path::cache('clang.cache'));

$REX['CUR_CLANG']  = rex_request('clang','rex-clang-id', $REX['START_CLANG_ID']);

if(rex_request('article_id', 'int') == 0)
  $REX['ARTICLE_ID'] = $REX['START_ARTICLE_ID'];
else
  $REX['ARTICLE_ID'] = rex_request('article_id','rex-article-id', $REX['NOTFOUND_ARTICLE_ID']);
