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

$object_id = $article_id > 0 ? $article_id : $category_id;
$object = rex_article::get($object_id, $clang);
if ($object) {
    $tree = $object->getParentTree();
    if (!$object->isStartarticle()) {
        $tree[] = $object;
    }
    foreach ($tree as $parent) {
        $id = $parent->getId();
        if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($id)) {
            $n = [];
            $n['title'] = str_replace(' ', '&nbsp;', htmlspecialchars($parent->getName()));
            if ($parent->isStartarticle()) {
                $n['href'] = rex_url::backendPage('structure', ['category_id' => $id, 'clang' => $clang]);
            }
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
