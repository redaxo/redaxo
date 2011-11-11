<?php

/**
 * Dient zur Ausgabe des Sprachen-blocks
 * @package redaxo5
 * @version svn:$Id$
 */

// rechte einbauen
// admin[]
// clang[xx], clang[0]
// rex::getUser()->hasPerm("csw[0]")

$num_clang = rex_clang::count();

$stop = false;
$languages = array();
if ($num_clang>1)
{

  if (!rex::getUser()->isAdmin() && !rex::getUser()->getComplexPerm('clang')->hasPerm($clang)) 
  {
    $stop = true;
    foreach(rex_clang::getAll() as $key => $val)
    {
      if(rex::getUser()->getComplexPerm('clang')->hasPerm($key))
      {
        $clang = $key;
        $stop = false;
        break;
      }
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
  
  }

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
}
else
{
  $clang = 0;
}

$langfragment = new rex_fragment();
$langfragment->setVar('languages', $languages, false);
echo $langfragment->parse('structure/languages');
unset($langfragment);
