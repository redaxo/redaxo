<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// ----- ob caching start für output filter
ob_start();
ob_implicit_flush(0);

// ----------------- MAGIC QUOTES CHECK
// require rex_path::src('core/functions/function_rex_mquotes.inc.php');

// --------------------------- ini settings

// Setzten des arg_separators, falls Sessions verwendet werden,
// um XHTML valide Links zu produzieren
@ini_set('arg_separator.input', '&amp;');
@ini_set('arg_separator.output', '&amp;');

// --------------------------- globals

include rex_path::src('config/master.inc.php');

// ----- INCLUDE ADDONS
include_once rex_path::src('config/addons.inc.php');

if($REX['SETUP'])
{
	header('Location:redaxo/');
	exit();
}

$REX['ARTICLE'] = new rex_article;
$REX['ARTICLE']->setCLang($REX['CUR_CLANG']);

if($REX['SETUP'])
{
	header('Location: redaxo/index.php');
	exit();
}elseif ($REX["ARTICLE"]->setArticleId($REX['ARTICLE_ID']))
{
	echo $REX["ARTICLE"]->getArticleTemplate();
}else
{
	echo 'Kein Startartikel selektiert / No starting Article selected. Please click here to enter <a href="redaxo/index.php">redaxo</a>';
	$REX['STATS'] = 0;
}

// ----- caching end für output filter
$CONTENT = ob_get_contents();
ob_end_clean();

// ----- inhalt ausgeben
rex_send_article($REX['ARTICLE'], $CONTENT, 'frontend');