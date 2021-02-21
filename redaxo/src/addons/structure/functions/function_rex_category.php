<?php

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge.
 *
 * @package redaxo5
 */

/** @psalm-suppress RedundantCondition */
assert(isset($articleId) && is_int($articleId));
/** @psalm-suppress RedundantCondition */
assert(isset($categoryId) && is_int($categoryId));
/** @psalm-suppress RedundantCondition */
assert(isset($clang) && is_int($clang));

$KATout = ''; // Variable definiert und vorbelegt wenn nicht existent
$KAToutARR = []; // Variable definiert und vorbelegt wenn nicht existent

$navigation = [];

/** @psalm-suppress RedundantCondition */
$objectId = $articleId > 0 ? $articleId : $categoryId;
$object = rex_article::get($objectId, $clang);
if ($object) {
    $tree = $object->getParentTree();
    if (!$object->isStartarticle()) {
        $tree[] = $object;
    }
    foreach ($tree as $parent) {
        $id = $parent->getId();
        if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($id)) {
            $n = [];
            $n['title'] = str_replace(' ', '&nbsp;', rex_escape($parent->getName()));
            if ($parent->isStartarticle()) {
                $n['href'] = rex_url::backendPage('structure', ['category_id' => $id, 'clang' => $clang]);
            }
            $navigation[] = $n;
        }
    }
}

$title = '<a class="rex-link-expanded" href="' . rex_url::backendPage('structure', ['category_id' => 0, 'clang' => $clang]) . '"><i class="rex-icon rex-icon-structure-root-level"></i> ' . rex_i18n::msg('root_level') . '</a>';

$fragment = new rex_fragment();
$fragment->setVar('id', 'rex-js-structure-breadcrumb', false);
$fragment->setVar('title', $title, false);
$fragment->setVar('items', $navigation, false);
echo $fragment->parse('core/navigations/breadcrumb.php');

unset($fragment);
unset($navigation);
