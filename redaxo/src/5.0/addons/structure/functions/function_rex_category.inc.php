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

$ooCat = rex_ooCategory::getCategoryById($category_id, $clang);
if($ooCat)
{
  foreach($ooCat->getParentTree() as $parent)
  {
    $catid = $parent->getId();
    if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($catid))
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
$path = $fragment->parse('navi_path');
unset($fragment);
unset($navi);

$KATout .= $path;
$KATout .= '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - END *** -->';