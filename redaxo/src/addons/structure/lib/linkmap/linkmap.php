<?php

/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_linkmap_category_tree extends rex_linkmap_tree_renderer
{
    private $context;

    public function __construct(rex_context $context)
    {
        $this->context = $context;
    }

    protected function treeItem(rex_category $cat, $liClasses, $linkClasses, $subHtml, $liIcon)
    {
        if ($liClasses != '') {
            $liClasses = ' class="' . rtrim($liClasses) . '"';
        }

        if ($linkClasses != '') {
            $linkClasses = ' class="' . rtrim($linkClasses) . '"';
        }

        $label = self::formatLabel($cat);

        $countChildren = count($cat->getChildren());
        $badgeCat = ($countChildren > 0) ? '<span class="badge">' . $countChildren . '</span>' : '';
        $li = '';
        $li .= '<li' . $liClasses . '>';
        $li .= '<a' . $linkClasses . ' href="' . $this->context->getUrl(['category_id' => $cat->getId()]) . '">' . $liIcon . htmlspecialchars($label) . '</a>';
        $li .= $badgeCat;
        $li .= $subHtml;
        $li .= '</li>' . "\n";

        return $li;
    }
}

/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_linkmap_article_list extends rex_linkmap_article_list_renderer
{
    private $context;

    public function __construct(rex_context $context)
    {
        $this->context = $context;
    }

    protected function listItem(rex_article $article, $category_id)
    {
        $liAttr = ' class="list-group-item"';
        $url = 'javascript:insertLink(\'redaxo://' . $article->getId() . '\',\'' . addslashes(htmlspecialchars(trim(sprintf('%s [%s]', $article->getName(), $article->getId())))) . '\');';
        return rex_linkmap_tree_renderer::formatLi($article, $category_id, $this->context, $liAttr, ' href="' . $url . '"') . '</li>' . "\n";
    }
}
