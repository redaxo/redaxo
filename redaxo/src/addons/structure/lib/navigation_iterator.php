<?php

/**
 * Klasse zum Erstellen von Navigationen, v0.1.
 *
 * benötigt PHP7!
 *
 * @package redaxo\structure
 */

/*
 * Beispiel-Konfiguration einer Haupt-Navigation:
 *
 * $it = rex_navigation_iterator::factory()
 *   ->ignoreOfflines(true)
 * ;
 *
 * foreach($it as $navItem) {
 *    // @var rex_article|rex_category $ooObj das aktuelle Element 
 *    // @var int $depth die aktuelle tiefe/ebene 
 *    // @var int $nth ein forlaufender Zähler je Ebene 
 *    // @var int $count Anzahl Elemente auf der aktuellen Ebene 
 *    list($ooObj, $depth, $nth, $count) = $navItem;
 *
 *    if ($nth == 1) {
 *        if ($depth == 1) {
 *           $class = "nav navbar-nav";
 *        } else {
 *           $class = "dropdown";
 *        }
 *        echo '<ul class="'. $class .'">';
 *    }
 *
 *    echo "<li class='rex-navi-depth-". $depth ." rex-nav-". $nth ."nth-child'>";
 *    echo "<a href='". $ooObj->getUrl() ."'>". $ooObj->getName() ."</a>";
 *    echo "</li>\n";
 *
 *    if ($nth == $count) {
 *          echo '</ul>';
 *    }
 * }
 * 
 * Bespiel-Konfiguration einer Seiten-Navigation
 *
 * $it = rex_navigation_iterator::factory()
 *   ->startCategory(27)
 *   ->depthLimit(3)
 *   ->ignoreOfflines(true)
 *
 * foreach($it as $navItem) {
 *    // @var rex_article|rex_category $ooObj das aktuelle Element
 *    // @var int $depth die aktuelle tiefe/ebene
 *    // @var int $nth ein forlaufender Zähler je Ebene
 *    // @var int $count Anzahl Elemente auf der aktuellen Ebene
 *    list($ooObj, $depth, $nth, $count) = $navItem;
 *
 *    if ($nth == 1) {
 *        if ($depth == 1) {
 *           $class = "nav side-nav";
 *        } else {
 *           $class = "";
 *        }
 *        echo '<ul class="'. $class .'">';
 *    }
 *
 *    echo "<li class='rex-navi-depth-". $depth ." rex-nav-". $nth ."nth-child'>";
 *    echo "<a href='". $ooObj->getUrl() ."'>". $ooObj->getName() ."</a>";
 *    echo "</li>\n";
 *
 *    if ($nth == $count) {
 *          echo '</ul>';
 *    }
 * }
 * 
 * Bespiel-Konfiguration einer Breadcrumb-Navigation
 *
 * $it = rex_navigation_iterator::factory()
 *   ->activePath(true)
 *   ->ignoreOfflines(true)
 * 
 * echo '<ul class="breadcrumb-nav">';
 * echo '<li><a href="' . rex_getUrl(rex_article::getSiteStartArticleId()) . '">STARTSEITE</a></li>';
 * foreach($it as $navItem) {
 *    // @var rex_article|rex_category $ooObj das aktuelle Element
 *    // @var int $depth die aktuelle tiefe/ebene
 *    // @var int $nth ein forlaufender Zähler je Ebene
 *    // @var int $count Anzahl Elemente auf der aktuellen Ebene
 *    list($ooObj, $depth, $nth, $count) = $navItem;
 *
 *    echo "<li>";
 *    echo "<a href='". $ooObj->getUrl() ."'>". $ooObj->getName() ."</a>";
 *    echo "</li>\n";
 * }
 * echo '</ul>';
 */
class rex_navigation_iterator implements IteratorAggregate
{
    use rex_factory_trait;

    private $startCategory = 0;
    private $ignoreOfflines = false;
    private $activePath = false;
    private $depthLimit = -1; // Wieviele Ebene tief, ab der Startebene
    private $path = [];
    private $filter = [];

    private $current_category_id = -1; // Aktuelle Katgorie

    private function __construct()
    {
        // nichts zu tun
    }

    /**
     * @return static
     */
    public static function factory()
    {
        $class = self::getFactoryClass();
        return new $class();
    }

    /**
     * @param int $categoryId Id der Wurzelkategorie, -1 für alle Kategorien (default)
     *
     * @return $this
     */
    public function startCategory($categoryId) {
        $this->startCategory = $categoryId;

        return $this;
    }

    /**
     * @param int $limit Anzahl der Ebenen die angezeigt werden sollen, -1 kein Limit (default)
     *
     * @return $this
     */
    public function depthLimit($limit) {
        $this->depthLimit = $limit;

        return $this;
    }

    /**
     * @param bool $ignoreOfflines FALSE, wenn offline Elemente angezeigt werden (default), sonst TRUE
     *
     * @return $this
     */
    public function ignoreOfflines($ignoreOfflines) {
        $this->ignoreOfflines = $ignoreOfflines;

        return $this;
    }

    /**
     * @param bool $activePath True, wenn nur Elemente der aktiven Kategorie angezeigt werden sollen, sonst FALSE (default)
     *
     * @return $this
     */
    public function activePath($activePath) {
        $this->activePath = $activePath;

        return $this;
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

    public function getIterator()
    {
        $this->_setActivePath();

        if ($this->ignoreOfflines) {
            $this->addFilter('status', 1, '==');
        }

        // 'yield from' is not supported by our min-php version
        foreach($this->_getNavigation($this->startCategory) as $item) {
            yield $item;
        }
    }

    private function _setActivePath()
    {
        $article_id = rex_article::getCurrentId();
        if (!$OOArt = rex_article::get($article_id)) {
            throw new Exception("Unable to determine current article-id");
        }

        $path = trim($OOArt->getPath(), '|');

        $this->path = [];
        if ($path != '') {
            $this->path = explode('|', $path);
        }

        $this->current_category_id = $OOArt->getCategoryId();
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

    protected function _getNavigation($category_id, $depth = 1)
    {
        if ($category_id < 1) {
            $nav_obj = rex_category::getRootCategories();
        } else {
            $nav_obj = rex_category::get($category_id)->getChildren();
        }

        $checked = [];
        foreach ($nav_obj as $nav) {
            if ($this->checkFilter($nav, $depth)) {
                $checked[] = $nav;
            }
        }

        $nth = 1;
        foreach($checked as $nav) {
            if (!$this->activePath || $this->activePath && ($nav->getId() == $this->current_category_id || in_array($nav->getId(), $this->path))
            ) {
                yield [$nav, $depth, $nth, count($checked)];

                ++$depth;
                if ($this->depthLimit >= $depth || $this->depthLimit < 0) {
                    // 'yield from' is not supported by our min-php version
                    foreach($this->_getNavigation($nav->getId(), $depth) as $item) {
                        yield $item;
                    }
                }
                --$depth;

                $nth++;
            }
        }
    }
}
