<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- INCLUDE FUNCTIONS
require_once rex_path::core('functions.inc.php');

// ----------------- VERSION

rex_core::setProperty('version', 5);
rex_core::setProperty('subversion', 0);
rex_core::setProperty('minorversion', 0);

rex_core::setProperty('redaxo', $REX['REDAXO']);

$config = rex_file::getConfig(rex_path::src('config.yml'));
foreach($config as $key => $value)
{
  rex_core::setProperty($key, $value);
}

rex_core::setProperty('fileperm', octdec(rex_core::getProperty('fileperm', '0664')));
rex_core::setProperty('dirperm', octdec(rex_core::getProperty('dirperm', '0775')));

date_default_timezone_set(rex_core::getProperty('timezone', 'Europe/Berlin'));

// ----------------- OTHER STUFF
rex_core::setProperty('setup_packages', array('be_style', 'be_style/agk_skin'));
rex_core::setProperty('system_packages', array('structure', 'structure/content', 'structure/linkmap', 'modules', 'templates', 'mediapool', 'import_export', 'metainfo', 'be_search', 'be_style', 'be_style/agk_skin', 'image_manager', 'users'));
rex_core::setProperty('mediapool', array('blocked_extension' => array('.php','.php3','.php4','.php5','.php6','.phtml','.pl','.asp','.aspx','.cfm','.jsp')));

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

$REX['CUR_CLANG']  = rex_request('clang','rex-clang-id', rex_core::getProperty('start_clang_id'));

if(rex_request('article_id', 'int') == 0)
  $REX['ARTICLE_ID'] = rex_core::getProperty('start_article_id');
else
  $REX['ARTICLE_ID'] = rex_request('article_id','rex-article-id', rex_core::getProperty('notfound_article_id'));
