<?php

/**
 * Klasse regelt den Zugriff auf Artikelinhalte.
 * DB Anfragen werden vermieden, caching läuft über generated Dateien.
 *
 * @package redaxo\structure\content
 */
class rex_article_content extends rex_article_content_base
{
    // bc schalter
    /** @var bool */
    private $viasql;

    /**
     * @var rex_article_slice|null
     * @phpstan-ignore-next-line this property looks unread, but is written from content cache file
     */
    private $currentSlice;

    /**
     * @param int|null $articleId
     * @param int|null $clang
     */
    public function __construct($articleId = null, $clang = null)
    {
        $this->viasql = false;
        parent::__construct($articleId, $clang);
    }

    // bc

    /**
     * @param bool $viasql
     * @return void
     */
    public function getContentAsQuery($viasql = true)
    {
        if (!$viasql) {
            $viasql = false;
        }
        $this->viasql = $viasql;
    }

    public function setArticleId($articleId)
    {
        // bc
        if ($this->viasql) {
            return parent::setArticleId($articleId);
        }

        $articleId = (int) $articleId;
        $this->article_id = $articleId;

        $rexArticle = rex_article::get($articleId, $this->clang);
        if ($rexArticle instanceof rex_article) {
            $this->category_id = $rexArticle->getCategoryId();
            $this->template_id = $rexArticle->getTemplateId();
            return true;
        }

        $this->article_id = 0;
        $this->template_id = 0;
        $this->category_id = 0;
        return false;
    }

    public function getValue($value)
    {
        // bc
        if ($this->viasql) {
            return parent::getValue($value);
        }

        $value = $this->correctValue($value);

        if (!rex_article::hasValue($value)) {
            throw new rex_exception('Articles do not have the property "'.$value.'"');
        }

        $article = rex_article::get($this->article_id, $this->clang);

        if (!$article) {
            throw new rex_exception('Article for id='.$this->article_id.' and clang='.$this->clang.' does not exist');
        }

        return $article->getValue($value);
    }

    public function hasValue($value)
    {
        // bc
        if ($this->viasql) {
            return parent::hasValue($value);
        }

        $value = $this->correctValue($value);

        return rex_article::hasValue($value);
    }

    public function getArticle($curctype = -1)
    {
        // bc
        if ($this->viasql) {
            return parent::getArticle($curctype);
        }

        $this->ctype = $curctype;

        if (!$this->getSlice && 0 != $this->article_id) {
            // article caching
            ob_start();
            try {
                ob_implicit_flush(false);

                $articleContentFile = rex_path::addonCache('structure', $this->article_id . '.' . $this->clang . '.content');

                if (!is_file($articleContentFile)) {
                    rex_content_service::generateArticleContent($this->article_id, $this->clang);
                }

                require $articleContentFile;
            } finally {
                $CONTENT = ob_get_clean();
                assert(is_string($CONTENT));
            }
        } else {
            // Inhalt ueber sql generierens
            $CONTENT = parent::getArticle($curctype);
        }

        return rex_extension::registerPoint(new rex_extension_point('ART_CONTENT', $CONTENT, [
            'ctype' => $curctype,
            'article' => $this,
        ]));
    }

    public function getCurrentSlice(): rex_article_slice
    {
        if ($this->viasql) {
            return parent::getCurrentSlice();
        }

        if (!$this->currentSlice) {
            throw new rex_exception('There is no current slice; getCurrentSlice() can be called only while rendering slices');
        }

        return $this->currentSlice;
    }
}
