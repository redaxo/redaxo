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

    public function __construct($articleId = null, $clang = null)
    {
        $this->viasql = false;
        parent::__construct($articleId, $clang);
    }

    // bc
    public function getContentAsQuery($viasql = true)
    {
        if (true !== $viasql) {
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

            $articleContentFile = rex_path::addonCache('structure', $this->article_id . '.' . $this->clang . '.content');

            if (!is_file($articleContentFile)) {
                rex_content_service::generateArticleContent($this->article_id, $this->clang);
            }

            require $articleContentFile;

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
