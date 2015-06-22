<?php

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge.
 *
 * @package redaxo5
 */

$KATout = ''; // Variable definiert und vorbelegt wenn nicht existent
$KAToutARR = []; // Variable definiert und vorbelegt wenn nicht existent


$navigation = [];

$ooCat = rex_category::get($category_id, $clang);
if ($ooCat) {
    foreach ($ooCat->getParentTree() as $parent) {
        $catid = $parent->getId();
        if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($catid)) {
            $n = [];
            $n['title'] = str_replace(' ', '&nbsp;', htmlspecialchars($parent->getName()));
            $n['href'] = rex_url::backendPage('structure', ['category_id' => $catid, 'clang' => $clang]);
            $navigation[] = $n;
        }
    }
}

$title = '<a href="' . rex_url::backendPage('structure', ['category_id' => 0, 'clang' => $clang]) . '"><i class="rex-icon rex-icon-sitestartarticle"></i> ' . rex_i18n::msg('homepage') . '</a>';

$fragment = new rex_fragment();
$fragment->setVar('title', $title, false);
$fragment->setVar('items', $navigation, false);
echo $fragment->parse('core/navigations/breadcrumb.php');

unset($fragment);
unset($navigation);
