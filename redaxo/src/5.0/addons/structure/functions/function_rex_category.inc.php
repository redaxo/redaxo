<?php

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge
 * @package redaxo5
 * @version svn:$Id$
 */

$KATebene = 0; // aktuelle Ebene: default
$KATout = ''; // Variable definiert und vorbelegt wenn nicht existent
$KAToutARR = array(); // Variable definiert und vorbelegt wenn nicht existent

// link to root kategory
$KAToutARR[]['content'] = '<a href="index.php?page=structure&amp;category_id=0&amp;clang='. $clang .'"'. rex_tabindex() .'>Homepage</a>';

$KAT = rex_sql::factory();
// $KAT->debugsql = true;
$KAT->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."article WHERE id=$category_id AND startpage=1 AND clang=$clang");

if ($KAT->getRows()!=1)
{
  // kategorie existiert nicht
  if($category_id != 0)
  {
    $category_id = 0;
    $article_id = 0;
  }
}
else
{
  // kategorie existiert

  $KPATH = explode('|',$KAT->getValue('path'));

  $KATebene = count($KPATH)-1;
  for ($ii=1;$ii<$KATebene;$ii++)
  {
    $SKAT = rex_sql::factory();
    $SKAT->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX'] .'article WHERE id='. $KPATH[$ii] .' AND startpage=1 AND clang='. $clang);

    if ($SKAT->getRows()==1)
    {
      $catname = str_replace(' ', '&nbsp;', htmlspecialchars($SKAT->getValue('catname')));
      $catid = $SKAT->getValue('id');
      if ($REX['USER']->hasCategoryPerm($catid))
      {
        $KAToutARR[]['content'] = '<a href="index.php?page=structure&amp;category_id='. $catid .'&amp;clang='. $clang .'"'. rex_tabindex() .'>'. $catname .'</a>';
      }
    }
  }

  if ($REX['USER']->hasCategoryPerm($category_id))
  {
    $catname = str_replace(' ', '&nbsp;', htmlspecialchars($KAT->getValue('catname')));

    $KAToutARR[]['content'] = '<a href="index.php?page=structure&amp;category_id='. $category_id .'&amp;clang='. $clang .'"'. rex_tabindex() .'>'. $catname .'</a>';
  }
  else
  {
    $category_id = 0;
    $article_id = 0;
  }
}

$KATout = '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - START *** -->';

/*	ul-Liste erstellen */
$list = array();
$list[1]['items'] = $KAToutARR;

/*	dl-Liste erstellen  */
$navi = array();
$navi['items'][rex_i18n::msg('path')] = $list;

$fragment = new rex_fragment();
$fragment->setVar('list', $navi, false);
$dl_list = $fragment->parse('core_navi_path');
//$dl_list = preg_replace('/(?:(?<=\>)|(?<=\/\>))(\s+)(?=\<\/?)/', '', $dl_list);
unset($fragment);

$KATout .= $dl_list;
$KATout .= '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - END *** -->';