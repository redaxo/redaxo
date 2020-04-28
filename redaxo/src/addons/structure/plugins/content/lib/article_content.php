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
    private $viasql;

    public function __construct($article_id = null, $clang = null)
    {
        $this->viasql = false;
        parent::__construct($article_id, $clang);
    }

    // bc
    public function getContentAsQuery($viasql = true)
    {
        if (true !== $viasql) {
            $viasql = false;
        }
        $this->viasql = $viasql;
    }

    public function setArticleId($article_id)
    {
        // bc
        if ($this->viasql) {
            return parent::setArticleId($article_id);
        }

        $article_id = (int) $article_id;
        $this->article_id = $article_id;

        $rex_article = rex_article::get($article_id, $this->clang);
        if ($rex_article instanceof rex_article) {
            $this->category_id = $rex_article->getCategoryId();
            $this->template_id = $rex_article->getTemplateId();
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

        if (rex_article::hasValue($value)) {
            return rex_article::get($this->article_id, $this->clang)->getValue($value);
        }
        return '[' . $value . ' not found]';
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
            // ----- start: article caching
            ob_start();
            ob_implicit_flush(0);

            $article_content_file = rex_path::addonCache('structure', $this->article_id . '.' . $this->clang . '.content');

            if (!file_exists($article_content_file)) {
                rex_content_service::generateArticleContent($this->article_id, $this->clang);
            }

            require $article_content_file;

            // ----- end: article caching
            $CONTENT = ob_get_clean();
        } else {
            // Inhalt ueber sql generierens
            $CONTENT = parent::getArticle($curctype);
        }

        $CONTENT = rex_extension::registerPoint(new rex_extension_point('ART_CONTENT', $CONTENT, [
            'ctype' => $curctype,
            'article' => $this,
        ]));

        return $CONTENT;
    }
}
