<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- SERVER VARS

// Setupservicestatus - if everything ok -> false; if problem set to true;
$REX['SETUP'] = true;
$REX['SERVER'] = "redaxo.org";
$REX['SERVERNAME'] = "REDAXO";
$REX['VERSION'] = "5";
$REX['SUBVERSION'] = "0";
$REX['MINORVERSION'] = "0";
$REX['ERROR_EMAIL'] = "jan.kristinus@yakamara.de";
$REX['FILEPERM'] = octdec(664); // oktaler wert
$REX['DIRPERM'] = octdec(775); // oktaler wert
$REX['INSTNAME'] = "rex20110323195019";
$REX['SESSION_DURATION'] = 3000;

// Is set first time SQL Object ist initialised
$REX['MYSQL_VERSION'] = "";

// default article id
$REX['START_ARTICLE_ID'] = 1;

// if there is no article -> change to this article
$REX['NOTFOUND_ARTICLE_ID'] = 1;

// default clang id
$REX['START_CLANG_ID'] = 0;

// default template id, if > 0 used as default, else template_id determined by inheritance
$REX['DEFAULT_TEMPLATE_ID'] = 0;

// default language
$REX['LANG'] = "de_de";

// activate frontend mod_rewrite support for url-rewriting
// Boolean: true/false
$REX['MOD_REWRITE'] = false;

// activate gzip output support
// reduces amount of data need to be send to the client, but increases cpu load of the server
$REX['USE_GZIP'] = "false"; // String: "true"/"false"/"fronted"/"backend"

// activate e-tag support
// tag content with a cache key to improve usage of client cache
$REX['USE_ETAG'] = "true"; // String: "true"/"false"/"fronted"/"backend"

// activate last-modified support
// tag content with a last-modified timestamp to improve usage of client cache
$REX['USE_LAST_MODIFIED'] = "true"; // String: "true"/"false"/"fronted"/"backend"

// activate md5 checksum support
// allow client to validate content integrity
$REX['USE_MD5'] = "true"; // String: "true"/"false"/"fronted"/"backend"

// Prefixes
$REX['TABLE_PREFIX']  = 'rex_';
$REX['TEMP_PREFIX']   = 'tmp_';

// Passwortverschluesselung
$REX['PSWFUNC'] = "sha1";

// bei fehllogin 5 sekunden kein relogin moeglich
$REX['RELOGINDELAY'] = 5;

// maximal erlaubte login versuche
$REX['MAXLOGINS'] = 50;

// maximal erlaubte anzahl an sprachen
$REX['MAXCLANGS'] = 15;

// Page auf die nach dem Login weitergeleitet wird
$REX['START_PAGE'] = 'structure';

// Zeitzone setzen
$REX['TIMEZONE'] = "Europe/Berlin";

date_default_timezone_set($REX['TIMEZONE']);

// ----------------- OTHER STUFF
//$REX['SYSTEM_ADDONS'] = array('structure', 'modules', 'templates', 'mediapool', 'import_export', 'metainfo', 'be_search', 'be_style', 'image_manager', 'users');
$REX['SYSTEM_PACKAGES'] = array('structure', array('structure', 'content'), array('structure', 'linkmap'), 'modules', 'templates', 'mediapool', 'import_export', 'metainfo', 'be_search', 'be_style', array('be_style', 'base'), array('be_style', 'agk_skin'), 'image_manager', 'users');
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

// ----- standard variables
$REX['VARIABLES'] = array();
$REX['VARIABLES'][] = 'rex_var_config';

// ----------------- default values
if (!isset($REX['NOFUNCTIONS'])) $REX['NOFUNCTIONS'] = false;

// ----------------- INCLUDE FUNCTIONS
if(!$REX['NOFUNCTIONS']) include_once rex_path::src('core/functions.inc.php');

// ----- SET CLANG
$REX['CLANG'] = array();
$clangFile = rex_path::generated('files/clang.cache');
if(file_exists($clangFile))
  $REX['CLANG'] = json_decode(rex_get_file_contents(rex_path::generated('files/clang.cache')), true);

$REX['CUR_CLANG']  = rex_request('clang','rex-clang-id', $REX['START_CLANG_ID']);

if(rex_request('article_id', 'int') == 0)
  $REX['ARTICLE_ID'] = $REX['START_ARTICLE_ID'];
else
  $REX['ARTICLE_ID'] = rex_request('article_id','rex-article-id', $REX['NOTFOUND_ARTICLE_ID']);
