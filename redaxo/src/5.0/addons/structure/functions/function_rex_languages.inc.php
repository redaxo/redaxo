<?php

/**
 * Dient zur Ausgabe des Sprachen-blocks
 * @package redaxo5
 * @version svn:$Id$
 */

// rechte einbauen
// admin[]
// clang[xx], clang[0]
// $REX['USER']->hasPerm("csw[0]")

reset($REX['CLANG']);
$num_clang = count($REX['CLANG']);

$stop = false;
$languages = array();
if ($num_clang>1)
{
  $i = 1;
  foreach($REX['CLANG'] as $key => $val)
  {
     $lang = array();
     $lang['id'] = $key;
     $lang['name'] = rex_i18n::translate($val);

     $lang['class'] = '';
     if($i == 1)
       $lang['class'] = 'rex-navi-first';

     $lang['url'] = '';
     if (!$REX['USER']->isAdmin() && !$REX['USER']->hasPerm('clang[all]') && !$REX['USER']->hasPerm('clang['. $key .']'))
     {
       if ($clang == $key)
       {
         $stop = true;
         break;
      }
     }
     else
     {
       $class = '';
       if ($key==$clang) $class = 'rex-active';

       $lang['link_class'] = $class;
       $lang['url'] = 'index.php?page='. $REX["PAGE"] .'&amp;clang='. $key . $sprachen_add .'&amp;ctype='. $ctype;
     }
     $i++;
     $languages[] = $lang;
  }
}
else
{
  $clang = 0;
}

if ($stop)
{
  echo '
<!-- *** OUTPUT OF CLANG-VALIDATE - START *** -->
      '. rex_warning('You have no permission to this area') .'
<!-- *** OUTPUT OF CLANG-VALIDATE - END *** -->
';
  exit;
}
else if ($num_clang>1)
{
  $langfragment = new rex_fragment();
  $langfragment->setVar('languages', $languages, false);
  echo $langfragment->parse('structure/languages');
  unset($langfragment);
}