<?php

/**
 * Klasse zum Erstellen von Navigationen, v0.1.
 *
 * @package redaxo\structure
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

class rex_navigation
{
    use rex_factory_trait;

    private $depth; // Wieviele Ebene tief, ab der Startebene
    private $open; // alles aufgeklappt, z.b. Sitemap
    private $path = [];
    private $classes = [];
    private $linkclasses = [];
    private $activeClass = 'rex-active';
    private $currentClass = 'rex-current';
    private $normalClass = 'rex-normal';
    private $ulClass = '';
    private $filter = [];
    private $callbacks = [];

    private $current_article_id = -1; // Aktueller Artikel
    private $current_category_id = -1; // Aktuelle Katgorie

    private function __construct()
    {
        $this->activeLiClass = $this->activeClass;
        $this->activeAClass = $this->activeClass;
        $this->currentLiClass = $this->currentClass;
        $this->currentAClass = $this->currentClass;
    }

    public static function factory()
    {
        $class = self::getFactoryClass();
        return new $class();
    }

    /**
     * Generiert eine Navigation.
     *
     * @param int  $category_id     Id der Wurzelkategorie
     * @param int  $depth           Anzahl der Ebenen die angezeigt werden sollen
     * @param bool $open            True, wenn nur Elemente der aktiven Kategorie angezeigt werden sollen, sonst FALSE
     * @param bool $ignore_offlines FALSE, wenn offline Elemente angezeigt werden, sonst TRUE
     *
     * @return string
     */
    public function get($category_id = 0, $depth = 3, $open = false, $ignore_offlines = false)
    {
        if (!$this->_setActivePath()) {
            return false;
        }

        $this->depth = $depth;
        $this->open = $open;
        if ($ignore_offlines) {
            $this->addFilter('status', 1, '==');
        }

        return $this->_getNavigation($category_id);
    }

    /**
     * @see get()
     */
    public function show($category_id = 0, $depth = 3, $open = false, $ignore_offlines = false)
    {
        echo $this->get($category_id, $depth, $open, $ignore_offlines);
    }

    /**
     * Generiert eine Breadcrumb-Navigation.
     *
     * @param string $startPageLabel Label der Startseite, falls FALSE keine Start-Page anzeigen
     * @param bool   $includeCurrent True wenn der aktuelle Artikel enthalten sein soll, sonst FALSE
     * @param int    $category_id    Id der Wurzelkategorie
     *
     * @return string
     */
    public function getBreadcrumb($startPageLabel, $includeCurrent = false, $category_id = 0)
    {
        if (!$this->_setActivePath()) {
            return false;
        }

        $path = $this->path;

        $i = 1;
        $lis = '';

        if ($startPageLabel) {
            $lis .= '<li class="rex-lvl' . $i . '"><a href="' . rex_getUrl(rex_article::getSiteStartArticleId()) . '">' . htmlspecialchars($startPageLabel) . '</a></li>';
            ++$i;

            // StartArticle nicht doppelt anzeigen
            if (isset($path[0]) && $path[0] == rex_article::getSiteStartArticleId()) {
                unset($path[0]);
            }
        }

        foreach ($path as $pathItem) {
            $cat = rex_category::get($pathItem);
            $lis .= '<li class="rex-lvl' . $i . '"><a href="' . $cat->getUrl() . '">' . htmlspecialchars($cat->getName()) . '</a></li>';
            ++$i;
        }

        if ($includeCurrent) {
            if ($art = rex_article::get($this->current_article_id)) {
                if (!$art->isStartArticle()) {
                    $lis .= '<li class="rex-lvl' . $i . '">' . htmlspecialchars($art->getName()) . '</li>';
                }
            } else {
                $cat = rex_category::get($this->current_article_id);
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

    public function setActiveClass($class)
    {
        $this->activeClass = $class;
    }

    public function setCurrentClass($class)
    {
        $this->currentClass = $class;
    }

    public function setNormalClass($class)
    {
        $this->normalClass = $class;
    }

    public function setActiveAClass($class)
    {
        $this->activeAClass = $class;
    }

    public function setActiveLiClass($class)
    {
        $this->activeLiClass = $class;
    }

    public function setCurrentLiClass($class)
    {
        $this->currentLiClass = $class;
    }

    public function setCurrentAClass($class)
    {
        $this->currentAClass = $class;
    }

    public function setUlClass($class)
    {
        $this->ulClass = $class;
    }

    /**
     * Fügt einen Filter hinzu.
     *
     * @param string     $metafield Datenbankfeld der Kategorie
     * @param mixed      $value     Wert für den Vergleich
     * @param string     $type      Art des Vergleichs =/</.
     * @param int|string $depth     "" wenn auf allen Ebenen, wenn definiert, dann wird der Filter nur auf dieser Ebene angewendet
     */
    public function addFilter($metafield = 'id', $value = '1', $type = '=', $depth = '')
    {
        $this->filter[] = ['metafield' => $metafield, 'value' => $value, 'type' => $type, 'depth' => $depth];
    }
    /**
     * Fügt einen Callback hinzu.
     *
     * @param callable   $callback z.B. myFunc oder myClass::myMethod
     * @param int|string $depth    "" wenn auf allen Ebenen, wenn definiert, dann wird der Filter nur auf dieser Ebene angewendet
     */
    public function addCallback($callback, $depth = '')
    {
        if ($callback != '') {
            $this->callbacks[] = ['callback' => $callback, 'depth' => $depth];
        }
    }

    private function _setActivePath()
    {
        $article_id = rex_article::getCurrentId();
        if ($OOArt = rex_article::get($article_id)) {
            $path = trim($OOArt->getPath(), '|');

            $this->path = [];
            if ($path != '') {
                $this->path = explode('|', $path);
            }

            $this->current_article_id = $article_id;
            $this->current_category_id = $OOArt->getCategoryId();
            return true;
        }

        return false;
    }

    private function checkFilter(rex_category $category, $depth)
    {
        foreach ($this->filter as $f) {
            if ($f['depth'] == '' || $f['depth'] == $depth) {
                $mf = $category->getValue($f['metafield']);
                $va = $f['value'];
                switch ($f['type']) {
                    case '<>':
                    case '!=':
                        if ($mf == $va) {
                            return false;
                        }
                        break;
                    case '>':
                        if ($mf <= $va) {
                            return false;
                        }
                        break;
                    case '<':
                        if ($mf >= $va) {
                            return false;
                        }
                        break;
                    case '=>':
                    case '>=':
                        if ($mf < $va) {
                            return false;
                        }
                        break;
                    case '=<':
                    case '<=':
                        if ($mf > $va) {
                            return false;
                        }
                        break;
                    case 'regex':
                        if (!preg_match($va, $mf)) {
                            return false;
                        }
                        break;
                    case '=':
                    case '==':
                    default:
                        // =
                        if ($mf != $va) {
                            return false;
                        }
                }
            }
        }
        return true;
    }

    private function checkCallbacks(rex_category $category, $depth, &$li, &$a)
    {
        foreach ($this->callbacks as $c) {
            if ($c['depth'] == '' || $c['depth'] == $depth) {
                $callback = $c['callback'];
                if (is_string($callback)) {
                    $callback = explode('::', $callback, 2);
                    if (count($callback) < 2) {
                        $callback = $callback[0];
                    }
                }
                if (is_array($callback) && count($callback) > 1) {
                    list($class, $method) = $callback;
                    if (is_object($class)) {
                        $result = $class->$method($category, $depth, $li, $a);
                    } else {
                        $result = $class::$method($category, $depth, $li, $a);
                    }
                } else {
                    $result = $callback($category, $depth, $li, $a);
                }
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function _getNavigation($category_id, $depth = 1)
    {
        if ($category_id < 1) {
            $nav_obj = rex_category::getRootCategories();
        } else {
            $nav_obj = rex_category::get($category_id)->getChildren();
        }

        $return = '';

        if (count($nav_obj) > 0) {
            $return .= '<ul class="rex-navi' . ($depth + 1) . '">';
        }

        $lis = [];
        foreach ($nav_obj as $nav) {
            $li = [];
            $a = [];
            $li['class'] = [];
            $a['class'] = [];
            $a['href'] = [$nav->getUrl()];
            if ($this->checkFilter($nav, $depth) && $this->checkCallbacks($nav, $depth, $li, $a)) {
                $li['class'][] = 'rex-article-' . $nav->getId();
                // classes abhaengig vom pfad
                if ($nav->getId() == $this->current_category_id) {
                    $li['class'][] = $this->currentLiClass;
                    $a['class'][] = $this->currentAClass;
                } elseif (in_array($nav->getId(), $this->path)) {
                    $li['class'][] = $this->activeLiClass;
                    $a['class'][] = $this->activeAClass;
                } else {
                    $li['class'][] = $this->normalClass;
                }
                if (isset($this->linkclasses[($depth - 1)])) {
                    $a['class'][] = $this->linkclasses[($depth - 1)];
                }
                if (isset($this->classes[($depth - 1)])) {
                    $li['class'][] = $this->classes[($depth - 1)];
                }
                $li_attr = [];
                foreach ($li as $attr => $v) {
                    $li_attr[] = $attr . '="' . implode(' ', $v) . '"';
                }
                $a_attr = [];
                foreach ($a as $attr => $v) {
                    $a_attr[] = $attr . '="' . implode(' ', $v) . '"';
                }
                $l = '<li ' . implode(' ', $li_attr) . '>';
                $l .= '<a ' . implode(' ', $a_attr) . '>' . htmlspecialchars($nav->getName()) . '</a>';
                ++$depth;
                if (($this->open ||
                        $nav->getId() == $this->current_category_id ||
                        in_array($nav->getId(), $this->path))
                    && ($this->depth >= $depth || $this->depth < 0)
                ) {
                    $l .= $this->_getNavigation($nav->getId(), $depth);
                }
                --$depth;
                $l .= '</li>';
                $lis[] = $l;
            }
        }
        if (count($lis) > 0) {
            return '<ul class="rex-navi' . $depth . ' rex-navi-depth-' . $depth . ' rex-navi-has-' . count($lis) . '-elements ' . $this->ulClass .'">' . implode('', $lis) . '</ul>';
        }
        return '';
    }
}
