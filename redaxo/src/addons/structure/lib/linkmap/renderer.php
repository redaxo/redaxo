<?php

/**
 * @package redaxo\structure
 *
 * @internal
 */
abstract class rex_linkmap_tree_renderer
{
    /**
     * @return string
     */
    public function getTree($category_id)
    {
        $category = rex_category::get($category_id);

        $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpointCategories();
        if (count($mountpoints) > 0) {
            $roots = $mountpoints;
            if (!$category && 1 === count($roots)) {
                $category = $roots[0];
            }
        } else {
            $roots = rex_category::getRootCategories();
        }

        $tree = [];
        if ($category) {
            foreach ($category->getParentTree() as $cat) {
                $tree[] = $cat->getId();
            }
        }

        $rendered = $this->renderTree($roots, $tree);
        // add css class to root node
        return '<ul class="list-group rex-linkmap-list-group"' . substr($rendered, 3);
    }

    /**
     * Returns the markup of a tree structure, with $children as root categories and respecing $activeTreeIds as the active path.
     *
     * @param rex_category[] $children      A array of rex_category objects representing the top level objects
     * @param int[]          $activeTreeIds
     *
     * @return string the rendered markup
     */
    public function renderTree(array $children, array $activeTreeIds)
    {
        $ul = '';
        if (is_array($children)) {
            $li = '';
            $ulclasses = 'list-group';
            foreach ($children as $cat) {
                $cat_children = $cat->getChildren();
                $cat_id = $cat->getId();
                $liclasses = 'list-group-item';
                $linkclasses = '';
                $sub_li = '';
                $liIcon = '<i class="rex-icon rex-icon-category"></i> ';

                $linkclasses .= $cat->isOnline() ? 'rex-online ' : 'rex-offline ';
                if (is_array($activeTreeIds) && in_array($cat_id, $activeTreeIds)) {
                    $sub_li = $this->renderTree($cat_children, $activeTreeIds);
                    $liIcon = '<i class="rex-icon rex-icon-open-category"></i> ';
                    $linkclasses .= 'rex-active ';
                }

                $li .= $this->treeItem($cat, $liclasses, $linkclasses, $sub_li, $liIcon);
            }

            if ('' != $ulclasses) {
                $ulclasses = ' class="' . rtrim($ulclasses) . '"';
            }

            if ('' != $li) {
                $ul = '<ul' . $ulclasses . ' data-cat-id="' . $children[0]->getParentId() . '">' . "\n" . $li . '</ul>' . "\n";
            }
        }
        return $ul;
    }

    abstract protected function treeItem(rex_category $cat, $liClasses, $linkClasses, $subHtml, $liIcon);

    /**
     * @return string
     */
    public static function formatLabel(rex_structure_element $OOobject)
    {
        $label = $OOobject->getName();

        if ('' == trim($label)) {
            $label = '&nbsp;';
        }

        if ($OOobject instanceof rex_article && !$OOobject->hasTemplate()) {
            $label .= ' [' . rex_i18n::msg('linkmap_has_no_template') . ']';
        }

        return $label;
    }

    /**
     * @return string
     */
    public static function formatLi(rex_structure_element $OOobject, $current_category_id, rex_context $context, $liAttr = '', $linkAttr = '')
    {
        $linkAttr .= ' class="' . ($OOobject->isOnline() ? 'rex-online' : 'rex-offline') . '"';

        if (false === strpos($linkAttr, ' href=')) {
            $linkAttr .= ' href="' . $context->getUrl(['category_id' => $OOobject->getId()]) . '"';
        }

        $label = self::formatLabel($OOobject);

        $icon = '<i class="rex-icon rex-icon-' . ($OOobject->isSiteStartArticle() ? 'sitestartarticle' : ($OOobject->isStartArticle() ? 'startarticle' : 'article')) . '"></i>';

        return '<li' . $liAttr . '><a' . $linkAttr . '>' . $icon . ' ' . rex_escape($label) . '<span class="list-item-suffix">'.$OOobject->getId().'</span></a>';
    }
}

/**
 * @package redaxo\structure
 *
 * @internal
 */
abstract class rex_linkmap_article_list_renderer
{
    public function getList($category_id)
    {
        $isRoot = 0 === $category_id;
        $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();

        if ($isRoot && 1 === count($mountpoints)) {
            $category_id = reset($mountpoints);
            $isRoot = false;
        }

        if ($isRoot && 0 == count($mountpoints)) {
            $articles = rex_article::getRootArticles();
        } elseif ($isRoot) {
            $articles = [];
        } else {
            $articles = rex_category::get($category_id)->getArticles();
        }
        return self::renderList($articles, $category_id);
    }

    /**
     * @return string
     */
    public function renderList(array $articles, $category_id)
    {
        $list = '';
        if ($articles) {
            foreach ($articles as $article) {
                $list .= $this->listItem($article, $category_id);
            }

            if ('' != $list) {
                $list = '<ul class="list-group rex-linkmap-list-group">' . $list . '</ul>';
            }
        }
        return $list;
    }

    abstract protected function listItem(rex_article $article, $category_id);
}
