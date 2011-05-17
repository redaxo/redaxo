<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- INCLUDE FUNCTIONS
require_once rex_path::core('functions.inc.php');

// ----------------- VERSION

rex::setProperty('version', 5);
rex::setProperty('subversion', 0);
rex::setProperty('minorversion', 0);

rex::setProperty('redaxo', $REX['REDAXO']);

$config = rex_file::getConfig(rex_path::src('config.yml'));
foreach($config as $key => $value)
{
  rex::setProperty($key, $value);
}

rex::setProperty('fileperm', octdec(rex::getProperty('fileperm', '0664')));
rex::setProperty('dirperm', octdec(rex::getProperty('dirperm', '0775')));

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

// ----------------- OTHER STUFF
rex::setProperty('setup_packages', array('be_style', 'be_style/agk_skin'));
rex::setProperty('system_packages', array('structure', 'structure/content', 'structure/linkmap', 'modules', 'templates', 'mediapool', 'import_export', 'metainfo', 'be_search', 'be_style', 'be_style/agk_skin', 'image_manager', 'users'));

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

$REX['CUR_CLANG']  = rex_request('clang','rex-clang-id', rex::getProperty('start_clang_id'));

if(rex_request('article_id', 'int') == 0)
  $REX['ARTICLE_ID'] = rex::getProperty('start_article_id');
else
  $REX['ARTICLE_ID'] = rex_request('article_id','rex-article-id', rex::getProperty('notfound_article_id'));
