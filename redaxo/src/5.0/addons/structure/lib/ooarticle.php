<?php

/**
 * Object Oriented Framework: Bildet einen Artikel der Struktur ab
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_ooArticle extends rex_ooRedaxo
{
  public function __construct($params = FALSE, $clang = FALSE)
  {
    parent :: __construct($params, $clang);
  }

  /**
   * CLASS Function:
   * Return an rex_ooRedaxo object based on an id
   *
   * @return rex_ooArticle
   */
  static public function getArticleById($article_id, $clang = FALSE)
  {
    return parent :: getById($article_id, $clang);
  }

  /**
   * CLASS Function:
   * Return the site wide start article
   *
   * @return rex_ooArticle
   */
  static public function getSiteStartArticle($clang = FALSE)
  {
    return parent :: getById(rex::getProperty('start_article_id'), $clang);
  }

  /**
   * CLASS Function:
   * Return start article for a certain category
   *
   * @return rex_ooArticle
   */
  static public function getCategoryStartArticle($a_category_id, $clang = FALSE)
  {
    return parent :: getById($a_category_id, $clang);
  }

  /**
   * CLASS Function:
   * Return a list of articles for a certain category
   *
   * @return array[rex_ooArticle]
   */
  static public function getArticlesOfCategory($a_category_id, $ignore_offlines = FALSE, $clang = FALSE)
  {
    if ($clang === FALSE)
    {
      $clang = rex_clang::getId();
    }

    $articlelist = rex_path::cache('articles/'.$a_category_id.".".$clang.".alist");
    if(!file_exists($articlelist))
    {
      rex_article_cache::generateLists($a_category_id, $clang);
    }

    $artlist = array ();
    if(file_exists($articlelist))
    {
      if(!isset($REX['RE_ID'][$a_category_id]))
      {
        $REX['RE_ID'][$a_category_id] = rex_file::getCache($articlelist);
      }

      if(isset($REX['RE_ID'][$a_category_id]))
      {
        foreach ($REX['RE_ID'][$a_category_id] as $var)
        {
          $article = self :: getArticleById($var, $clang);
          if ($ignore_offlines)
          {
            if ($article->isOnline())
            {
              $artlist[] = $article;
            }
          }
          else
          {
            $artlist[] = $article;
          }
        }
      }
    }

    return $artlist;
  }

  /**
   * CLASS Function:
   * Return a list of top-level articles
   *
   * @return array[rex_ooArticle]
   */
  static public function getRootArticles($ignore_offlines = FALSE, $clang = FALSE)
  {
    return self :: getArticlesOfCategory(0, $ignore_offlines, $clang);
  }

  /**
   * Accessor Method:
   * returns the category id
   *
   * @return int
   */
  public function getCategoryId()
  {
    return $this->isStartPage() ? $this->getId() : $this->getParentId();
  }

  /**
   * Object Function:
   * Returns the parent category
   *
   * @return rex_ooCategory
   */
  public function getCategory()
  {
    return rex_ooCategory :: getCategoryById($this->getCategoryId(), $this->getClang());
  }

  /**
   * Accessor Method:
   * returns the parent object of the article
   *
   * @return rex_ooArticle
   */
  public function getParent($clang = false)
  {
    if ($clang === FALSE)
    {
      $clang = rex_clang::getId();
    }

    return rex_ooArticle::getArticleById($this->_re_id, $clang);
  }

  /**
   * Accessor Method:
   * returns the path of the category/article
   *
   * @return string
   */
  public function getPath()
  {
    if($this->isStartArticle())
    {
      return $this->_path.$this->_id .'|';
    }

    return $this->_path;
  }

  /**
   * Static Method: Returns True when the given article is a valid rex_ooArticle
   *
   * @return boolean
   */
  static public function isValid($article)
  {
    return is_object($article) && is_a($article, 'rex_ooArticle');
  }

  /**
   * @see rex_ooRedaxo::getValue()
   *
   * @return string
   */
  public function getValue($value)
  {
    // alias für re_id -> category_id
    if(in_array($value, array('re_id', '_re_id', 'category_id', '_category_id')))
    {
      // für die CatId hier den Getter verwenden,
      // da dort je nach ArtikelTyp unterscheidungen getroffen werden müssen
      return $this->getCategoryId();
    }
    return parent::getValue($value);
  }

  /**
   * @param string $value
   *
   * @return string
   */
  static public function hasValue($value)
  {
    return parent::_hasValue($value, array('art_'));
  }
}