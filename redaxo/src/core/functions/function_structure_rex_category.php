<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge.
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
$object = Article::get($objectId, $clang);
if ($object) {
    $tree = $object->getParentTree();
    if (!$object->isStartarticle()) {
        $tree[] = $object;
    }
    foreach ($tree as $parent) {
        $id = $parent->getId();
        if (Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($id)) {
            $n = [];
            $n['title'] = str_replace(' ', '&nbsp;', rex_escape($parent->getName()));
            if ($parent->isStartarticle()) {
                $n['href'] = Url::backendPage('structure', ['category_id' => $id, 'clang' => $clang]);
            }
            $navigation[] = $n;
        }
    }
}

$title = '<a class="rex-link-expanded" href="' . Url::backendPage('structure', ['category_id' => 0, 'clang' => $clang]) . '"><i class="rex-icon rex-icon-structure-root-level"></i> ' . I18n::msg('root_level') . '</a>';

$fragment = new Fragment();
$fragment->setVar('id', 'rex-js-structure-breadcrumb', false);
$fragment->setVar('title', $title, false);
$fragment->setVar('items', $navigation, false);
echo $fragment->parse('core/navigations/breadcrumb.php');

unset($fragment);
unset($navigation);
