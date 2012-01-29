<?php

/**
 * Dient zur Ausgabe des Sprachen-blocks
 * @package redaxo5
 */

$num_clang = rex_clang::count();

$stop = false;
$languages = array();
if ($num_clang>1)
{
  $i = 1;
  foreach(rex_clang::getAll() as $key => $val)
  {

     if (rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('clang')->hasPerm($key))
     {
     	$lang = array();
		$lang['id'] = $key;
		$lang['title'] = rex_i18n::translate($val);
		
		$lang['linkClasses'] = array();
		if ($key==$clang) 
			$lang['linkClasses'][] = 'rex-active';
		
		$lang['itemClasses'] = $lang['linkClasses'];
		$lang['href'] = 'index.php?page='. rex::getProperty('page') .'&amp;clang='. $key . $sprachen_add .'&amp;ctype='. $ctype;
		
		$languages[] = $lang;

     }
     $i++;
  }
  
  $langfragment = new rex_fragment();
  $langfragment->setVar('type','switch');
  $langfragment->setVar('blocks', array( array('headline' => array("title" => rex_i18n::msg('languages')), 'navigation' => $languages)), false);
  echo $langfragment->parse('navigation.tpl');
  
  unset($langfragment);

}