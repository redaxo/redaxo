<?php

/**
 * Klasse zum Erstellen von Navigationen.
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
    private $filter = [];
    private $callbacks = [];

    private $current_article_id = -1; // Aktueller Artikel
    private $current_category_id = -1; // Aktuelle Katgorie

    private static $factoryCall = false;

    public function __construct()
    {
        if (!self::$factoryCall && self::class === static::class) {
            throw new rex_exception(sprintf('Base class %s must be instantiated via %1$s::factory().', self::class));
        }

        self::$factoryCall = false;
    }

    /**
     * @return static
     */
    public static function factory()
    {
        $class = self::getFactoryClass();
        self::$factoryCall = true;
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
        $lis = [];

        if ($startPageLabel) {
            $link = '<a href="'.rex_getUrl(rex_article::getSiteStartArticleId()).'">'.rex_escape($startPageLabel).'</a>';
            $lis[] = $this->getBreadcrumbListItemTag($link, [
                'class' => 'rex-lvl'.$i,
            ], $i);
            ++$i;

            // StartArticle nicht doppelt anzeigen
            if (isset($path[0]) && $path[0] == rex_article::getSiteStartArticleId()) {
                unset($path[0]);
            }
        }

        $show = !$category_id;
        foreach ($path as $pathItem) {
            if (!$show) {
                if ($pathItem == $category_id) {
                    $show = true;
                } else {
                    continue;
                }
            }

            $cat = rex_category::get($pathItem);
            $link = $this->getBreadcrumbLinkTag($cat, rex_escape($cat->getName()), [
                'href' => $cat->getUrl(),
            ], $i);
            $lis[] = $this->getBreadcrumbListItemTag($link, [
                'class' => 'rex-lvl'.$i,
            ], $i);
            ++$i;
        }

        if ($includeCurrent) {
            if ($art = rex_article::get($this->current_article_id)) {
                if (!$art->isStartArticle()) {
                    $lis[] = $this->getBreadcrumbListItemTag(rex_escape($art->getName()), [
                        'class' => 'rex-lvl'.$i,
                    ], $i);
                }
            } else {
                $cat = rex_category::get($this->current_article_id);
                $lis[] = $this->getBreadcrumbListItemTag(rex_escape($cat->getName()), [
                    'class' => 'rex-lvl'.$i,
                ], $i);
            }
        }

        return $this->getBreadcrumbListTag($lis, [
            'class' => [
                'rex-breadcrumb',
            ],
        ]);
    }

    /**
     * @see getBreadcrumb()
     */
    public function showBreadcrumb($startPageLabel = false, $includeCurrent = false, $category_id = 0)
    {
        echo $this->getBreadcrumb($startPageLabel, $includeCurrent, $category_id);
    }

    public function setClasses($classes)
    {
        $this->classes = $classes;
    }

    public function setLinkClasses($classes)
    {
        $this->linkclasses = $classes;
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
     *
     * @return $this
     */
    public function addCallback($callback, $depth = '')
    {
        if ('' != $callback) {
            $this->callbacks[] = ['callback' => $callback, 'depth' => $depth];
        }
        return $this;
    }

    private function _setActivePath()
    {
        $article_id = rex_article::getCurrentId();
        if ($OOArt = rex_article::get($article_id)) {
            $path = trim($OOArt->getPath(), '|');

            $this->path = [];
            if ('' != $path) {
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
            if ('' == $f['depth'] || $f['depth'] == $depth) {
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

    private function checkCallbacks(rex_category $category, $depth, &$li, &$a, &$a_content)
    {
        foreach ($this->callbacks as $c) {
            if ('' == $c['depth'] || $c['depth'] == $depth) {
                $callback = $c['callback'];
                if (is_string($callback)) {
                    $callback = explode('::', $callback, 2);
                    if (count($callback) < 2) {
                        $callback = $callback[0];
                    }
                }
                if (is_array($callback) && count($callback) > 1) {
                    [$class, $method] = $callback;
                    if (is_object($class)) {
                        $result = $class->$method($category, $depth, $li, $a, $a_content);
                    } else {
                        $result = $class::$method($category, $depth, $li, $a, $a_content);
                    }
                } else {
                    $result = $callback($category, $depth, $li, $a, $a_content);
                }
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param int $category_id
     * @param int $depth
     *
     * @return string
     */
    protected function _getNavigation($category_id, $depth = 1)
    {
        if ($category_id < 1) {
            $nav_obj = rex_category::getRootCategories();
        } else {
            $nav_obj = rex_category::get($category_id)->getChildren();
        }

        $lis = [];
        foreach ($nav_obj as $nav) {
            $li = [];
            $a = [];
            $li['class'] = [];
            $a['class'] = [];
            $a['href'] = [$nav->getUrl()];
            $a_content = rex_escape($nav->getName());
            if ($this->checkFilter($nav, $depth) && $this->checkCallbacks($nav, $depth, $li, $a, $a_content)) {
                $li['class'][] = 'rex-article-' . $nav->getId();
                // classes abhaengig vom pfad
                if ($nav->getId() == $this->current_category_id) {
                    $li['class'][] = 'rex-current';
                    $a['class'][] = 'rex-current';
                } elseif (in_array($nav->getId(), $this->path)) {
                    $li['class'][] = 'rex-active';
                    $a['class'][] = 'rex-active';
                } else {
                    $li['class'][] = 'rex-normal';
                }
                if (isset($this->linkclasses[($depth - 1)])) {
                    $a['class'][] = $this->linkclasses[($depth - 1)];
                }
                if (isset($this->classes[($depth - 1)])) {
                    $li['class'][] = $this->classes[($depth - 1)];
                }

                $link = $this->getLinkTag($nav, $a_content, $a, $depth);

                ++$depth;
                if (($this->open ||
                        $nav->getId() == $this->current_category_id ||
                        in_array($nav->getId(), $this->path))
                    && ($this->depth >= $depth || $this->depth < 0)
                ) {
                    $link .= "\n".$this->_getNavigation($nav->getId(), $depth);
                }
                --$depth;
                $lis[] = $this->getListItemTag($nav, $link, $li, $depth);
            }
        }
        if (count($lis) > 0) {
            return $this->getListTag($lis, [
                'class' => [
                    'rex-navi'.$depth,
                    'rex-navi-depth-'.$depth,
                    'rex-navi-has-'.count($lis).'-elements',
                ],
            ], $depth);
        }
        return '';
    }

    /**
     * @param string[] $items
     */
    protected function getBreadcrumbListTag(array $items, array $attributes): string
    {
        return '<ul'.rex_string::buildAttributes($attributes).">\n".implode('', $items)."</ul>\n";
    }

    protected function getBreadcrumbListItemTag(string $item, array $attributes, int $depth): string
    {
        return '<li'.rex_string::buildAttributes($attributes).'>'.$item."</li>\n";
    }

    protected function getBreadcrumbLinkTag(rex_category $category, string $content, array $attributes, int $depth): string
    {
        if (!isset($attributes['href'])) {
            $attributes['href'] = $category->getUrl();
        }

        return '<a'.rex_string::buildAttributes($attributes).'>'.$content.'</a>';
    }

    /**
     * @param string[] $items
     */
    protected function getListTag(array $items, array $attributes, int $depth): string
    {
        return '<ul'.rex_string::buildAttributes($attributes).">\n".implode('', $items)."</ul>\n";
    }

    protected function getListItemTag(rex_category $category, string $item, array $attributes, int $depth): string
    {
        return '<li'.rex_string::buildAttributes($attributes).'>'.$item."</li>\n";
    }

    protected function getLinkTag(rex_category $category, string $content, array $attributes, int $depth): string
    {
        if (!isset($attributes['href'])) {
            $attributes['href'] = $category->getUrl();
        }

        return '<a'.rex_string::buildAttributes($attributes).'>'.$content.'</a>';
    }
}
