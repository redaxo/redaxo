<?php

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge
 * @package redaxo5
 * @version svn:$Id$
 */

$KATout = ''; // Variable definiert und vorbelegt wenn nicht existent
$KAToutARR = array(); // Variable definiert und vorbelegt wenn nicht existent

// link to root kategory
$KAToutARR[]['content'] = '<a href="index.php?page=structure&amp;category_id=0&amp;clang='. $clang .'">Homepage</a>';

$KAT = rex_sql::factory();
// $KAT->debugsql = true;
$KAT->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."article WHERE id=$category_id AND startpage=1 AND clang=$clang");

if ($KAT->getRows()!=1 || !$REX['USER']->hasCategoryPerm($category_id))
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
  
  $ooCat = rex_ooCategory::getCategoryById($category_id, $clang);
  foreach($ooCat->getParentTree() as $parent)
  {
    $catid = $parent->getId();
    if ($REX['USER']->hasCategoryPerm($catid))
    {
      $catname = str_replace(' ', '&nbsp;', htmlspecialchars($parent->getName()));
      $KAToutARR[]['content'] = '<a href="index.php?page=structure&amp;category_id='. $catid .'&amp;clang='. $clang .'">'. $catname .'</a>';
    }
  }
}

$KATout = '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - START *** -->';

/*	ul-Liste erstellen */
$list = array();
$list[1]['items'] = $KAToutARR;
unset($KAToutARR);

/*	dl-Liste erstellen  */
$navi = array();
$navi['items'][rex_i18n::msg('path')] = $list;

$fragment = new rex_fragment();
$fragment->setVar('list', $navi, false);
$dl_list = $fragment->parse('core_navi_path');
//$dl_list = preg_replace('/(?:(?<=\>)|(?<=\/\>))(\s+)(?=\<\/?)/', '', $dl_list);
unset($fragment);
unset($navi);

$KATout .= $dl_list;
$KATout .= '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - END *** -->';