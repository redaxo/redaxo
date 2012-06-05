<?php

/**
 * Klasse zum Erstellen von Navigationen, v0.1
 *
 * @package redaxo5
 */

/*
 * Beispiel:
 *
 * UL, LI Navigation von der Rootebene aus,
 * 2 Ebenen durchgehen, Alle unternavis offen
 * und offline categorien nicht beachten
 *
 * Navigation:
 *
 * $nav = rex_navigation::factory();
 * $nav->setClasses(array('lev1', 'lev2', 'lev3'));
 * $nav->setLinkClasses(array('alev1', 'alev2', 'alev3'));
 * echo $nav->get(0,2,TRUE,TRUE);
 *
 * Sitemap:
 *
 * $nav = rex_navigation::factory();
 * $nav->show(0,-1,TRUE,TRUE);
 *
 * Breadcrump:
 *
 * $nav = rex_navigation::factory();
 * $nav->showBreadcrumb(true);
 */

class rex_navigation extends rex_factory_base
{
  private
    $depth, // Wieviele Ebene tief, ab der Startebene
    $open, // alles aufgeklappt, z.b. Sitemap
    $ignore_offlines,
    $path = array(),
    $classes = array(),

    $current_article_id = -1, // Aktueller Artikel
    $current_category_id = -1; // Aktuelle Katgorie

  private function __construct()
  {
    // nichts zu tun
  }

  static public function factory()
  {
    $class = self::getFactoryClass();
    return new $class();
  }

  /**
   * Generiert eine Navigation
   *
   * @param $category_id Id der Wurzelkategorie
   * @param $depth Anzahl der Ebenen die angezeigt werden sollen
   * @param $open True, wenn nur Elemente der aktiven Kategorie angezeigt werden sollen, sonst FALSE
   * @param $ignore_offlines FALSE, wenn offline Elemente angezeigt werden, sonst TRUE
   */
  public function get($category_id = 0, $depth = 3, $open = false, $ignore_offlines = false)
  {
    if (!$this->_setActivePath()) return false;

    $this->depth = $depth;
    $this->open = $open;
    $this->ignore_offlines = $ignore_offlines;

    return $this->_getNavigation($category_id, $this->ignore_offlines);
  }

  /**
   * @see get()
   */
  public function show($category_id = 0, $depth = 3, $open = false, $ignore_offlines = false)
  {
    echo $this->get($category_id, $depth, $open, $ignore_offlines);
  }

  /**
   * Generiert eine Breadcrumb-Navigation
   *
   * @param $startPageLabel Label der Startseite, falls FALSE keine Start-Page anzeigen
   * @param $includeCurrent True wenn der aktuelle Artikel enthalten sein soll, sonst FALSE
   * @param $category_id Id der Wurzelkategorie
   */
  public function getBreadcrumb($startPageLabel, $includeCurrent = false, $category_id = 0)
  {
    if (!$this->_setActivePath()) return false;

    $path = $this->path;

    $i = 1;
    $lis = '';

    if ($startPageLabel) {
      $lis .= '<li class="rex-lvl' . $i . '"><a href="' . rex_getUrl(rex::getProperty('start_article_id')) . '">' . htmlspecialchars($startPageLabel) . '</a></li>';
      $i++;

      // StartArticle nicht doppelt anzeigen
      if (isset($path[0]) && $path[0] == rex::getProperty('start_article_id')) {
        unset($path[0]);
      }
    }

    foreach ($path as $pathItem) {
      $cat = rex_category::getCategoryById($pathItem);
      $lis .= '<li class="rex-lvl' . $i . '"><a href="' . $cat->getUrl() . '">' . htmlspecialchars($cat->getName()) . '</a></li>';
      $i++;
    }

    if ($includeCurrent) {
      if ($art = rex_article::getArticleById($this->current_article_id))
        if (!$art->isStartpage()) {
          $lis .= '<li class="rex-lvl' . $i . '">' . htmlspecialchars($art->getName()) . '</li>';
        } else {
          $cat = rex_category::getCategoryById($this->current_article_id);
          $lis .= '<li class="rex-lvl' . $i . '">' . htmlspecialchars($cat->getName()) . '</li>';
        }
    }

    return '<ul class="rex-breadcrumb">' . $lis . '</ul>';
  }

  /**
   * @see getBreadcrumb()
   */
  public function showBreadcrumb($includeCurrent = false, $category_id = 0)
  {
    echo $this->getBreadcrumb($includeCurrent, $category_id);
  }

  public function setClasses($classes)
  {
    $this->classes = $classes;
  }

  public function setLinkClasses($classes)
  {
    $this->linkclasses = $classes;
  }

  private function _setActivePath()
  {
    $article_id = rex::getProperty('article_id');
    if ($OOArt = rex_article::getArticleById($article_id)) {
      $path = trim($OOArt->getPath(), '|');

      $this->path = array();
      if ($path != '')
        $this->path = explode('|', $path);

      $this->current_article_id = $article_id;
      $this->current_category_id = $OOArt->getCategoryId();
      return true;
    }

    return false;
  }

  protected function _getNavigation($category_id, $ignore_offlines = true)
  {
    static $depth = 0;

    if ($category_id < 1)
      $nav_obj = rex_category::getRootCategories($ignore_offlines);
    else
      $nav_obj = rex_category::getChildrenById($category_id, $ignore_offlines);

    $return = '';

    if (count($nav_obj) > 0)
      $return .= '<ul class="rex-navi' . ($depth + 1) . '">';

    foreach ($nav_obj as $nav) {
      $liClass = '';
      $linkClass = '';

      // classes abhaengig vom pfad
      if ($nav->getId() == $this->current_category_id) {
        $liClass .= ' rex-current';
        $linkClass .= ' rex-current';
      } elseif (in_array($nav->getId(), $this->path)) {
        $liClass .= ' rex-active';
        $linkClass .= ' rex-active';
      } else {
        $liClass .= ' rex-normal';
      }

      // classes abhaengig vom level
      if (isset($this->classes[$depth]))
        $liClass .= ' ' . $this->classes[$depth];

      if (isset($this->linkclasses[$depth]))
        $linkClass .= ' ' . $this->linkclasses[$depth];



      $linkClass = $linkClass == '' ? '' : ' class="' . ltrim($linkClass) . '"';

      $return .= '<li class="rex-article-' . $nav->getId() . $liClass . '">';
      $return .= '<a' . $linkClass . ' href="' . $nav->getUrl() . '">' . htmlspecialchars($nav->getName()) . '</a>';

      $depth++;
      if (($this->open ||
        $nav->getId() == $this->current_category_id ||
        in_array($nav->getId(), $this->path))
        && ($this->depth > $depth || $this->depth < 0)) {
        $return .= $this->_getNavigation($nav->getId(), $ignore_offlines);
      }
      $depth--;

      $return .= '</li>';
    }

    if (count($nav_obj) > 0)
      $return .= '</ul>';

    return $return;
  }
}
