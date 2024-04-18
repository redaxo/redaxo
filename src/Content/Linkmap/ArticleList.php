<?php

namespace Redaxo\Core\Content\Linkmap;

use Redaxo\Core\Content\Article;
use rex_context;

/**
 * @internal
 */
class ArticleList extends ArticleListRenderer
{
    public function __construct(
        private rex_context $context,
    ) {}

    /**
     * @return string
     */
    protected function listItem(Article $article, $categoryId)
    {
        $liAttr = ' class="list-group-item"';
        $url = 'javascript:insertLink(\'redaxo://' . $article->getId() . '\',\'' . rex_escape(trim(sprintf('%s [%s]', $article->getName(), $article->getId())), 'js') . '\');';
        return CategoryTreeRenderer::formatLi($article, $categoryId, $this->context, $liAttr, ' href="' . $url . '"') . '</li>' . "\n";
    }
}