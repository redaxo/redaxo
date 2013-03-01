<?php

/**
 * @package redaxo\structure
 */
abstract class rex_linkmap_tree_renderer
{
    public function getTree($category_id)
    {
        $tree = array();
        $category = rex_category::getCategoryById($category_id);

        if ($category) {
            foreach ($category->getParentTree() as $cat) {
                $tree[] = $cat->getId();
            }
        }

        $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
        if (count($mountpoints) > 0) {
            $roots = array();
            foreach ($mountpoints as $mp) {
                if (rex_category::getCategoryById($mp)) {
                    $roots[] = rex_category::getCategoryById($mp);
                }
            }
        } else {
            $roots = rex_category::getRootCategories();
        }

        $rendered = $this->renderTree($roots, $tree);
        // add css class to root node
        return '<ul class="rex-tree-root"' . substr($rendered, 3);
    }

    /**
     * Returns the markup of a tree structure, with $children as root categories and respecing $activeTreeIds as the active path.
     *
     * @param rex_category[] $children      A array of rex_category objects representing the top level objects
     * @param int[]          $activeTreeIds
     * @return string the rendered markup
     */
    public function renderTree(array $children, array $activeTreeIds)
    {
        $ul = '';
        if (is_array($children)) {
            $li = '';
            $ulclasses = '';
            if (count($children) == 1) $ulclasses .= 'rex-children-one ';
            foreach ($children as $cat) {
                $cat_children = $cat->getChildren();
                $cat_id = $cat->getId();
                $liclasses = '';
                $linkclasses = '';
                $sub_li = '';
                if (count($cat_children) > 0) {
                    $liclasses .= 'rex-children ';
                    $linkclasses .= 'rex-linkmap-is-not-empty ';
                }

                if (next($children) == null ) $liclasses .= 'rex-children-last ';
                $linkclasses .= $cat->isOnline() ? 'rex-online ' : 'rex-offline ';
                if (is_array($activeTreeIds) && in_array($cat_id, $activeTreeIds)) {
                    $sub_li = $this->renderTree($cat_children, $activeTreeIds);
                    $liclasses .= 'rex-active ';
                    $linkclasses .= 'rex-active ';
                }

                $li .= $this->treeItem($cat, $liclasses, $linkclasses, $sub_li);
            }

            if ($ulclasses != '')
                $ulclasses = ' class="' . rtrim($ulclasses) . '"';

            if ($li != '') $ul = '<ul' . $ulclasses . ' data-cat-id="' . $children[0]->getParentId() . '">' . "\n" . $li . '</ul>' . "\n";
        }
        return $ul;
    }

    abstract protected function treeItem(rex_category $cat, $liClasses, $linkClasses, $subHtml);

    public static function formatLabel(rex_structure_element $OOobject)
    {
        $label = $OOobject->getName();

        if (trim($label) == '')
        $label = '&nbsp;';

        if (rex::getUser()->hasPerm('advancedMode[]'))
        $label .= ' [' . $OOobject->getId() . ']';

        if ($OOobject instanceof rex_article && !$OOobject->hasTemplate())
        $label .= ' [' . rex_i18n::msg('lmap_has_no_template') . ']';

        return $label;
    }

    public static function formatLi(rex_structure_element $OOobject, $current_category_id, rex_context $context, $liAttr = '', $linkAttr = '')
    {
        $liAttr .= $OOobject->getId() == $current_category_id ? ' id="rex-linkmap-active"' : '';
        $linkAttr .= ' class="' . ($OOobject->isOnline() ? 'rex-online' : 'rex-offine') . '"';

        if (strpos($linkAttr, ' href=') === false)
        $linkAttr .= ' href="' . $context->getUrl(array('category_id' => $OOobject->getId())) . '"';

        $label = self::formatLabel($OOobject);

        return '<li' . $liAttr . '><a' . $linkAttr . '>' . htmlspecialchars($label) . '</a>';
    }
}

/**
 * @package redaxo\structure
 */
abstract class rex_linkmap_article_list_renderer
{
    public function getList($category_id)
    {
        $isRoot = $category_id === 0;
        $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();

        if ($isRoot && count($mountpoints) == 0) {
            $articles = rex_article::getRootArticles();
        } else {
            $articles = rex_article::getArticlesOfCategory($category_id);
        }
        return self::renderList($articles, $category_id);
    }

    public function renderList(array $articles, $category_id)
    {
        $list = null;
        if ($articles) {
            foreach ($articles as $article) {
                $list .= $this->listItem($article, $category_id);
            }

            if ($list != '') {
                $list = '<ul>' . $list . '</ul>';
            }
        }
        return $list;
    }

    abstract protected function listItem(rex_article $article, $category_id);
}
