<?php

/**
 * Klasse regelt den Zugriff auf Artikelinhalte.
 * DB Anfragen werden vermieden, caching läuft über generated Dateien.
 *
 * @package redaxo5
 */

class rex_article extends rex_article_base
{
  // bc schalter
  private $viasql;

  public function __construct($article_id = null, $clang = null)
  {
    $this->viasql = FALSE;
    parent::__construct($article_id, $clang);
  }

  // bc
  public function getContentAsQuery($viasql = TRUE)
  {
    if ($viasql !== TRUE) $viasql = FALSE;
    $this->viasql = $viasql;
  }

  public function setArticleId($article_id)
  {
    // bc
    if($this->viasql)
    {
      return parent::setArticleId($article_id);
    }

    $article_id = (int) $article_id;
    $this->article_id = $article_id;

    $rex_ooArticle = rex_ooArticle::getArticleById($article_id, $this->clang);
    if(rex_ooArticle::isValid($rex_ooArticle))
    {
      $this->category_id = $rex_ooArticle->getCategoryId();
      $this->template_id = $rex_ooArticle->getTemplateId();
      return TRUE;
    }

    $this->article_id = 0;
    $this->template_id = 0;
    $this->category_id = 0;
    return FALSE;
  }

  protected function correctValue($value)
  {
    // bc
    if($this->viasql)
    {
      return parent::correctValue($value);
    }

    if ($value == 'category_id')
    {
      if ($this->getValue('startpage')!=1) $value = 're_id';
      else $value = 'id';
    }

    return $value;
  }

  protected function _getValue($value)
  {
    // bc
    if($this->viasql)
    {
      return parent::_getValue($value);
    }

    $value = $this->correctValue($value);

    return rex_ooArticle::getArticleById($this->article_id, $this->clang)->getValue($value);
  }

  public function hasValue($value)
  {
    // bc
    if($this->viasql)
    {
      return parent::hasValue($value);
    }

    $value = $this->correctValue($value);

    return rex_ooArticle::getArticleById($this->article_id, $this->clang)->hasValue($value);
  }

  public function getArticle($curctype = -1)
  {
    // bc
    if($this->viasql)
    {
      return parent::getArticle($curctype);
    }

    global $REX;

    $this->ctype = $curctype;

    if (!$this->getSlice && $this->article_id != 0)
    {
      // ----- start: article caching
      ob_start();
      ob_implicit_flush(0);

      $article_content_file = rex_path::addonCache('structure', $this->article_id.'.'.$this->clang.'.content');
      if(!file_exists($article_content_file))
      {
        include_once rex_path::plugin('structure', 'content', 'functions/function_rex_content.inc.php');
        $generated = rex_content_service::generateArticleContent($this->article_id, $this->clang);
        if($generated !== true)
        {
          // fehlermeldung ausgeben
          echo $generated;
        }
      }

      if(file_exists($article_content_file))
      {
        require $article_content_file;
      }

      // ----- end: article caching
      $CONTENT = ob_get_contents();
      ob_end_clean();
    }
    else
    {
      // Inhalt ueber sql generierens
      $CONTENT = parent::getArticle($curctype);
    }

    return $CONTENT;
  }
}