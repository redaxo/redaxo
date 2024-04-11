<?php

namespace Redaxo\Core\Content\RexVar;

use Redaxo\Core\Content\Article as BaseArticle;
use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\Language\Language;
use Redaxo\Core\RexVar\RexVar;

use function in_array;

/**
 * REX_ARTICLE[1]
 * REX_ARTICLE[id=1].
 *
 * REX_ARTICLE[id=1 ctype=2 clang=1]
 *
 * REX_ARTICLE[field='id']
 * REX_ARTICLE[field='description' id=3]
 * REX_ARTICLE[field='description' id=3 clang=2]
 *
 * Attribute:
 *   - clang     => ClangId des Artikels festlegen
 *   - ctype     => Spalte des Artikels festlegen
 *   - field     => Nur dieses Feld des Artikels ausgeben
 */
class Article extends RexVar
{
    /**
     * Werte für die Ausgabe.
     */
    protected function getOutput()
    {
        $id = $this->getParsedArg('id', 0, true);
        $clang = $this->getParsedArg('clang', 'null');
        $ctype = $this->getParsedArg('ctype', -1);
        $field = $this->getParsedArg('field');

        $noId = 0 == $id;
        if ($noId) {
            $id = '$this->getValue(\'id\')';
        }

        if ($field) {
            return self::class . '::getArticleValue(' . $id . ', ' . $field . ', ' . $clang . ')';
        }
        if (!$noId || !in_array($this->getContext(), ['module', 'action'])) {
            // aktueller Artikel darf nur in Templates, nicht in Modulen eingebunden werden
            // => endlossschleife
            if ($noId && 'null' == $clang) {
                return '$this->getArticle(' . $ctype . ')';
            }
            return self::class . '::getArticle(' . $id . ', ' . $ctype . ', ' . $clang . ')';
        }

        return false;
    }

    /**
     * @return string|int|null
     */
    public static function getArticleValue($id, $field, $clang = null)
    {
        if (null === $clang) {
            $clang = Language::getCurrentId();
        }
        $article = BaseArticle::get($id, $clang);
        return rex_escape($article->getValue($field));
    }

    /**
     * @return string
     */
    public static function getArticle($id, $ctype = -1, $clang = null)
    {
        if (null === $clang) {
            $clang = Language::getCurrentId();
        }
        $article = new ArticleContent($id, $clang);
        return $article->getArticle($ctype);
    }
}
