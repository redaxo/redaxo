<?php

/**
 * Object Oriented Framework: Basisklasse für die Strukturkomponenten
 *
 * @package redaxo\structure
 */
abstract class rex_structure_element
{
    use rex_instance_pool_trait;

    /*
     * these vars get read out
     */
    protected $id = '';
    protected $parent_id = '';
    protected $clang = '';
    protected $name = '';
    protected $catname = '';
    protected $template_id = '';
    protected $path = '';
    protected $priority = '';
    protected $catpriority = '';
    protected $startarticle = '';
    protected $status = '';
    protected $updatedate = '';
    protected $createdate = '';
    protected $updateuser = '';
    protected $createuser = '';

    /**
     * @var array
     */
    private static $childIds = [];

    /**
     * Constructor
     *
     * @param array $params
     */
    protected function __construct(array $params)
    {
        foreach (self::getClassVars() as $var) {
            if (isset($params[$var])) {
                $this->$var = $params[$var];
            }
        }
    }

    /**
     * Returns Object Value
     *
     * @param string $value
     * @return string
     */
    public function getValue($value)
    {
        // damit alte rex_article felder wie teaser, online_from etc
        // noch funktionieren
        // gleicher BC code nochmals in article::getValue
        foreach (['', 'art_', 'cat_'] as $prefix) {
            $val = $prefix . $value;
            if (isset($this->$val)) {
                return $this->$val;
            }
        }
        return null;
    }

    /**
     * @param string $value
     * @param array  $prefixes
     * @return boolean
     */
    protected static function _hasValue($value, array $prefixes = [])
    {
        static $values = null;

        if (!$values) {
            $values = self::getClassVars();
        }

        if (in_array($value, $values)) {
            return true;
        }

        foreach ($prefixes as $prefix) {
            if (in_array($prefix . $value, $values)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an Array containing article field names
     *
     * @return string[]
     */
    public static function getClassVars()
    {
        static $vars = [];

        if (empty($vars)) {
            $vars = [];

            $startId = rex::getProperty('start_article_id');
            $file = rex_path::addonCache('structure',  $startId . '.1.article');
            if (!rex::isBackend() && file_exists($file)) {
                // da getClassVars() eine statische Methode ist, können wir hier nicht mit $this->getId() arbeiten!
                $genVars = self::convertGeneratedArray(rex_file::getCache($file), 1);
                unset($genVars['article_id']);
                unset($genVars['last_update_stamp']);
                foreach ($genVars as $name => $value) {
                    $vars[] = $name;
                }
            } else {
                // Im Backend die Spalten aus der DB auslesen / via EP holen
                $sql = rex_sql::factory();
                $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article LIMIT 0');
                foreach ($sql->getFieldnames() as $field) {
                    $vars[] = $field;
                }
            }
        }

        return $vars;
    }

    /**
     * Converts Genernated Array to OOBase Format Array
     *
     * @param array $generatedArray
     * @param int   $clang
     * @return array
     */
    private static function convertGeneratedArray(array $generatedArray, $clang)
    {
        $rex_structure_elementArray['id'] = $generatedArray['article_id'][$clang];
        $rex_structure_elementArray['clang'] = $clang;
        foreach ($generatedArray as $key => $var) {
            $rex_structure_elementArray[$key] = $var[$clang];
        }
        unset($rex_structure_elementArray['article_id']);
        return $rex_structure_elementArray;
    }

    /**
     * Return an rex_structure_element object based on an id.
     * The instance will be cached in an instance-pool and therefore re-used by a later call.
     *
     * @param int $id    the article id
     * @param int $clang the clang id
     * @return static A rex_structure_element instance typed to the late-static binding type of the caller
     */
    public static function get($id, $clang = null)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        if (!$clang) {
            $clang = rex_clang::getCurrentId();
        }

        $class = get_called_class();
        return static::getInstanceLazy(function ($id, $clang) use ($class) {
            $article_path = rex_path::addonCache('structure', $id . '.' . $clang . '.article');
            // generate cache if not exists
            if (!file_exists($article_path)) {
                rex_article_cache::generateMeta($id, $clang);
            }

            // article is valid, if cache exists after generation
            if (file_exists($article_path)) {
                // load metadata from cache
                $metadata = rex_file::getCache($article_path);
                // create object with the loaded metadata
                return new $class(self::convertGeneratedArray($metadata, $clang));
            }

            return null;
        }, $id, $clang);
    }

    /**
     * @param int    $parentId
     * @param string $listType
     * @param bool   $ignoreOfflines
     * @param int    $clang
     * @return static[]
     */
    protected static function getChildElements($parentId, $listType, $ignoreOfflines = false, $clang = null)
    {
        $parentId = (int) $parentId;

        if ($parentId < 0) {
            return [];
        }

        if (!$clang) {
            $clang = rex_clang::getCurrentId();
        }

        $listFile = rex_path::addonCache('structure', $parentId . '.' . $clang . '.' . $listType);

        $list = [];

        if (!file_exists($listFile)) {
            rex_article_cache::generateLists($parentId);
        }

        if (file_exists($listFile)) {
            if (!isset(self::$childIds[$listType][$parentId])) {
                self::$childIds[$listType][$parentId] = rex_file::getCache($listFile);
            }

            if (is_array(self::$childIds[$listType][$parentId])) {
                foreach (self::$childIds[$listType][$parentId] as $var) {
                    $element = static::get($var, $clang);
                    if ($element && (!$ignoreOfflines || $element->isOnline())) {
                        $list[] = $element;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Returns the clang of the category
     *
     * @return integer
     */
    public function getClang()
    {
        return $this->clang;
    }

    /**
     * Returns a url for linking to this article
     *
     * @param array|string $params
     * @param string       $divider
     * @return string
     */
    public function getUrl($params = '', $divider = '&amp;')
    {
        return rex_getUrl($this->getId(), $this->getClang(), $params, $divider);
    }

    /**
     * Returns the id of the article
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the parent_id of the article
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Returns the path of the category/article
     *
     * @return string
     */
    abstract public function getPath();

    /**
     * Returns the path ids of the category/article as an array
     *
     * @return int[]
     */
    public function getPathAsArray()
    {
        $path = explode('|', $this->getPath());
        return array_values(array_map('intval', array_filter($path)));
    }

    /**
     * Returns the parent category
     *
     * @param int $clang
     * @return self
     */
    abstract public function getParent($clang = null);

    /**
     * Returns the name of the article
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the article priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the last update user
     *
     * @return string
     */
    public function getUpdateUser()
    {
        return $this->updateuser;
    }

    /**
     * Returns the last update date
     *
     * @return int
     */
    public function getUpdateDate()
    {
        return $this->updatedate;
    }

    /**
     * Returns the creator
     *
     * @return string
     */
    public function getCreateUser()
    {
        return $this->createuser;
    }

    /**
     * Returns the creation date
     *
     * @return int
     */
    public function getCreateDate()
    {
        return $this->createdate;
    }

    /**
     * Returns true if article is online.
     *
     * @return boolean
     */
    public function isOnline()
    {
        return $this->status == 1;
    }

    /**
     * Returns the template id
     *
     * @return integer
     */
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * Returns true if article has a template.
     *
     * @return boolean
     */
    public function hasTemplate()
    {
        return $this->template_id > 0;
    }

    /**
     * Returns a link to this article
     *
     * @param array|string $params             Parameter für den Link
     * @param array        $attributes         Attribute die dem Link hinzugefügt werden sollen. Default: null
     * @param string       $sorroundTag        HTML-Tag-Name mit dem der Link umgeben werden soll, z.b. 'li', 'div'. Default: null
     * @param array        $sorroundAttributes Attribute die Umgebenden-Element hinzugefügt werden sollen. Default: null
     * @return string
     */
    public function toLink($params = '', array $attributes = null, $sorroundTag = null, array $sorroundAttributes = null)
    {
        $name = htmlspecialchars($this->getName());
        $link = '<a href="' . $this->getUrl($params) . '"' . $this->_toAttributeString($attributes) . ' title="' . $name . '">' . $name . '</a>';

        if ($sorroundTag !== null && is_string($sorroundTag)) {
            $link = '<' . $sorroundTag . $this->_toAttributeString($sorroundAttributes) . '>' . $link . '</' . $sorroundTag . '>';
        }

        return $link;
    }

    /**
     * @param array $attributes
     * @return string
     */
    protected function _toAttributeString(array $attributes)
    {
        $attr = '';

        if ($attributes !== null && is_array($attributes)) {
            foreach ($attributes as $name => $value) {
                $attr .= ' ' . $name . '="' . $value . '"';
            }
        }

        return $attr;
    }

    /**
     * Get an array of all parentCategories.
     * Returns an array of rex_structure_element objects.
     *
     * @return rex_category[]
     */
    public function getParentTree()
    {
        $return = [];

        if ($this->path) {
            if ($this->isStartArticle()) {
                $explode = explode('|', $this->path . $this->id . '|');
            } else {
                $explode = explode('|', $this->path);
            }

            if (is_array($explode)) {
                foreach ($explode as $var) {
                    if ($var != '') {
                        $return[] = rex_category::get($var, $this->clang);
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Checks if $anObj is in the parent tree of the object
     *
     * @param self $anObj
     * @return boolean
     */
    public function inParentTree(self $anObj)
    {
        $tree = $this->getParentTree();
        foreach ($tree as $treeObj) {
            if ($treeObj == $anObj) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if this Article is the Startpage for the category.
     *
     * @return boolean
     */
    public function isStartArticle()
    {
        return $this->startarticle;
    }

    /**
     * Returns true if this Article is the Startpage for the entire site.
     *
     * @return boolean
     */
    public function isSiteStartArticle()
    {
        return $this->id == rex::getProperty('start_article_id');
    }

    /**
     * Returns  true if this Article is the not found article
     *
     * @return boolean
     */
    public function isNotFoundArticle()
    {
        return $this->id == rex::getProperty('notfound_article_id');
    }
}
