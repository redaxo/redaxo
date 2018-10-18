<?php

/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_sitemap_category_tree extends rex_linkmap_tree_renderer
{
    private $context;

    public function __construct(rex_context $context)
    {
        $this->context = $context;
    }

    public function getTree($category_id)
    {
        // if not, let the structure as is, by providing a remembered id
        if ($category_id <= 0) {
            $category_id = rex_request::session('tree_category_id', 'int');
        } else {
            rex_request::setSession('tree_category_id', $category_id);
        }
        return parent::getTree($category_id);
    }

    protected function treeItem(rex_category $cat, $liClasses, $linkClasses, $subHtml, $liIcon)
    {
        $linkClasses .= '';

        if ($liClasses != '') {
            $liClasses = ' class="' . rtrim($liClasses) . '"';
        }

        if ($linkClasses != '') {
            $linkClasses = ' class="' . rtrim($linkClasses) . '"';
        }

        $label = self::formatLabel($cat);

        $li = '';
        $li .= '<li' . $liClasses . ' data-cat-id="' . $cat->getId() . '" data-parent-id="' . $cat->getParentId() . '" data-priority="' . $cat->getPriority() . '">';
        $li .= '<a' . $linkClasses . ' href="' . $this->context->getUrl(['toggle_category_id' => $cat->getId()] + rex_api_sitemap_tree::getUrlParams()) . '">&nbsp;</a>';
        $li .= '<a href="' . $this->context->getUrl(['category_id' => $cat->getId()]) . '">' . rex_escape($label) . '</a>';
        $li .= $subHtml;
        $li .= '</li>';

        return $li;
    }
}
