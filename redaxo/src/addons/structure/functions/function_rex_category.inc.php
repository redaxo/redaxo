<?php

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge
 * @package redaxo5
 */

$KATout = ''; // Variable definiert und vorbelegt wenn nicht existent
$KAToutARR = array(); // Variable definiert und vorbelegt wenn nicht existent

// link to root kategory
$navigation = array();
$navigation[] = array(
    'title' => rex_i18n::msg("homepage"),
    'href' => 'index.php?page=structure&amp;category_id=0&amp;clang='. $clang
  );

$ooCat = rex_ooCategory::getCategoryById($category_id, $clang);
if ($ooCat)
{
  foreach ($ooCat->getParentTree() as $parent)
  {
    $catid = $parent->getId();
    if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($catid))
    {
      $n = array();
      $n["title"] = str_replace(' ', '&nbsp;', htmlspecialchars($parent->getName()));
      $n["href"] = 'index.php?page=structure&amp;category_id='. $catid .'&amp;clang='. $clang;
      $navigation[] = $n;
    }
  }
}

$blocks = array();
$blocks[] = array(
      "headline" => array("title" => rex_i18n::msg('path')),
      "navigation" => $navigation
      );

$fragment = new rex_fragment();
$fragment->setVar('type','path');
$fragment->setVar('blocks', $blocks, false);
$path = $fragment->parse('navigation.tpl');

unset($fragment);
unset($navi);

$KATout .= $path;
$KATout .= '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - END *** -->';
