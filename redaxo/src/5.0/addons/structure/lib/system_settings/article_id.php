<?php

/**
 * Class for the start_article_id and notfound_article_id settings
 *
 * @author gharlan
 */
class rex_system_setting_article_id extends rex_system_setting
{
  private $key;

  /**
   * Constructor
   *
   * @param string $key Key
   */
  public function __construct($key)
  {
    $this->key = $key;
  }

  public function getKey()
  {
    return $this->key;
  }

  public function getClass()
  {
    return rex_plugin::get('structure', 'linkmap')->isAvailable() ? 'rex-form-widget' : 'rex-form-text';
  }

  public function getLabel()
  {
    return rex_i18n::msg('system_setting_'. $this->key);
  }

  public function getField()
  {
    if(rex_plugin::get('structure', 'linkmap')->isAvailable())
    {
      static $id = 1;
      return rex_var_link::_getLinkButton($this->getName(), $id++, rex::getProperty($this->key));
    }
    else
    {
      return '<input class="rex-form-text" type="text" id="'. $this->getId() .'" name="'. $this->getName() .'" value="'. htmlspecialchars(rex::getProperty($this->key)).'" />';
    }
  }

  public function isValid($value)
  {
    $article = rex_ooArticle::getArticleById($value);
    if(!rex_ooArticle::isValid($article))
    {
      return rex_i18n::msg('system_setting_'. $this->key .'_invalid');
    }
    return true;
  }

  public function cast($value)
  {
    return (integer) $value;
  }
}