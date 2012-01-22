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
     $lang = array();
     $lang['id'] = $key;
     $lang['name'] = rex_i18n::translate($val);

     $lang['class'] = '';
     if($i == 1)
       $lang['class'] = 'rex-navi-first';

     $lang['url'] = '';
     if (rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('clang')->hasPerm($key))
     {
       $class = '';
       if ($key==$clang) $class = 'rex-active';

       $lang['link_class'] = $class;
       $lang['url'] = 'index.php?page='. rex::getProperty('page') .'&amp;clang='. $key . $sprachen_add .'&amp;ctype='. $ctype;
     }
     $i++;
     $languages[] = $lang;
  }
  
  $langfragment = new rex_fragment();
  $langfragment->setVar('languages', $languages, false);
  echo $langfragment->parse('structure/languages');
  unset($langfragment);
}