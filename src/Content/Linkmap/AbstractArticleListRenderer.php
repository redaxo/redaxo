<?php

namespace Redaxo\Core\Content\Linkmap;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Core;

use function count;

/**
 * @internal
 */
abstract class AbstractArticleListRenderer
{
    /**
     * @return string
     */
    public function getList($categoryId)
    {
        $isRoot = 0 === $categoryId;
        $mountpoints = Core::requireUser()->getComplexPerm('structure')->getMountpoints();

        if ($isRoot && 1 === count($mountpoints)) {
            $categoryId = reset($mountpoints);
            $isRoot = false;
        }

        if ($isRoot && 0 == count($mountpoints)) {
            $articles = Article::getRootArticles();
        } elseif ($isRoot) {
            $articles = [];
        } else {
            $articles = Category::get($categoryId)->getArticles();
        }
        return self::renderList($articles, $categoryId);
    }

    /**
     * @return string
     */
    public function renderList(array $articles, $categoryId)
    {
        $list = '';
        if ($articles) {
            foreach ($articles as $article) {
                $list .= $this->listItem($article, $categoryId);
            }

            if ('' != $list) {
                $list = '<ul class="list-group rex-linkmap-list-group">' . $list . '</ul>';
            }
        }
        return $list;
    }

    /**
     * @return string
     */
    abstract protected function listItem(Article $article, $categoryId);
}
