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
  'title' => rex_i18n::msg('homepage'),
  'href' => rex_url::backendPage('structure', array('category_id' => 0, 'clang' => $clang))
);

$ooCat = rex_category::getCategoryById($category_id, $clang);
if ($ooCat) {
  foreach ($ooCat->getParentTree() as $parent) {
    $catid = $parent->getId();
    if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($catid)) {
      $n = array();
      $n['title'] = str_replace(' ', '&nbsp;', htmlspecialchars($parent->getName()));
      $n['href'] = rex_url::backendPage('structure', array('category_id' => $catid, 'clang' => $clang));
      $navigation[] = $n;
    }
  }
}


$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('path'));
$fragment->setVar('items', $navigation, false);
echo $fragment->parse('core/navigations/path.tpl');

unset($fragment);
unset($navigation);

echo '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - END *** -->';
