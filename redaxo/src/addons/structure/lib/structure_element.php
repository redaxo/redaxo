<?php

/**
 * Object Oriented Framework: Basisklasse für die Strukturkomponenten.
 *
 * @package redaxo\structure
 */
abstract class rex_structure_element
{
    use rex_instance_pool_trait;
    use rex_instance_list_pool_trait;

    /*
     * these vars get read out
     */
    protected $id = '';
    protected $parent_id = '';
    protected $clang_id = '';
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

    protected static $classVars;

    /**
     * Constructor.
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
     * Returns Object Value.
     *
     * @param string $value
     *
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
     *
     * @return bool
     */
    protected static function _hasValue($value, array $prefixes = [])
    {
        $values = self::getClassVars();

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
     * Returns an Array containing article field names.
     *
     * @return string[]
     */
    public static function getClassVars()
    {
        if (empty(self::$classVars)) {
            self::$classVars = [];

            $startId = rex_article::getSiteStartArticleId();
            $file = rex_path::addonCache('structure', $startId . '.1.article');
            if (!rex::isBackend() && file_exists($file)) {
                // da getClassVars() eine statische Methode ist, können wir hier nicht mit $this->getId() arbeiten!
                $genVars = rex_file::getCache($file);
                unset($genVars['last_update_stamp']);
                foreach ($genVars as $name => $value) {
                    self::$classVars[] = $name;
                }
            } else {
                // Im Backend die Spalten aus der DB auslesen / via EP holen
                $sql = rex_sql::factory();
                $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article LIMIT 0');
                foreach ($sql->getFieldnames() as $field) {
                    self::$classVars[] = $field;
                }
            }
        }

        return self::$classVars;
    }

    public static function resetClassVars()
    {
        self::$classVars = null;
    }

    /**
     * Return an rex_structure_element object based on an id.
     * The instance will be cached in an instance-pool and therefore re-used by a later call.
     *
     * @param int $id    the article id
     * @param int $clang the clang id
     *
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
        return static::getInstance([$id, $clang], function ($id, $clang) use ($class) {
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
                return new $class($metadata);
            }

            return null;
        });
    }

    /**
     * @param int    $parentId
     * @param string $listType
     * @param bool   $ignoreOfflines
     * @param int    $clang
     *
     * @return static[]
     */
    protected static function getChildElements($parentId, $listType, $ignoreOfflines = false, $clang = null)
    {
        $parentId = (int) $parentId;
        // for $parentId=0 root elements will be returned, so abort here for $parentId<0 only
        if (0 > $parentId) {
            return [];
        }
        if (!$clang) {
            $clang = rex_clang::getCurrentId();
        }

        $class = get_called_class();
        return static::getInstanceList(
            // list key
            [$parentId, $listType],
            // callback to get an instance for a given ID, status will be checked if $ignoreOfflines==true
            function ($id) use ($class, $ignoreOfflines, $clang) {
                if ($instance = $class::get($id, $clang)) {
                    return !$ignoreOfflines || $instance->isOnline() ? $instance : null;
                }
                return null;
            },
            // callback to create the list of IDs
            function ($parentId, $listType) {
                $listFile = rex_path::addonCache('structure', $parentId . '.' . $listType);
                if (!file_exists($listFile)) {
                    rex_article_cache::generateLists($parentId);
                }
                return rex_file::getCache($listFile);
            }
        );
    }

    /**
     * Returns the clang of the category.
     *
     * @return int
     *
     * @deprecated since redaxo 5.6, use getClangId() instead
     */
    public function getClang()
    {
        return $this->clang_id;
    }

    /**
     * Returns the clang of the category.
     *
     * @return int
     */
    public function getClangId()
    {
        return $this->clang_id;
    }

    /**
     * Returns a url for linking to this article.
     *
     * @param array  $params
     * @param string $divider
     *
     * @return string
     */
    public function getUrl(array $params = [], $divider = '&amp;')
    {
        return rex_getUrl($this->getId(), $this->getClang(), $params, $divider);
    }

    /**
     * Returns the id of the article.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the parent_id of the article.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Returns the path of the category/article.
     *
     * @return string
     */
    abstract public function getPath();

    /**
     * Returns the path ids of the category/article as an array.
     *
     * @return int[]
     */
    public function getPathAsArray()
    {
        $path = explode('|', $this->getPath());
        return array_values(array_map('intval', array_filter($path)));
    }

    /**
     * Returns the parent category.
     *
     * @return self
     */
    abstract public function getParent();

    /**
     * Returns the name of the article.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the article priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the last update user.
     *
     * @return string
     */
    public function getUpdateUser()
    {
        return $this->updateuser;
    }

    /**
     * Returns the last update date.
     *
     * @return int
     */
    public function getUpdateDate()
    {
        return $this->updatedate;
    }

    /**
     * Returns the creator.
     *
     * @return string
     */
    public function getCreateUser()
    {
        return $this->createuser;
    }

    /**
     * Returns the creation date.
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
     * @return bool
     */
    public function isOnline()
    {
        return $this->status == 1;
    }

    /**
     * Returns the template id.
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * Returns true if article has a template.
     *
     * @return bool
     */
    public function hasTemplate()
    {
        return $this->template_id > 0;
    }

    /**
     * Returns whether the element is permitted.
     *
     * @return bool
     */
    abstract public function isPermitted();

    /**
     * Returns a link to this article.
     *
     * @param array  $params             Parameter für den Link
     * @param array  $attributes         Attribute die dem Link hinzugefügt werden sollen. Default: array
     * @param string $sorroundTag        HTML-Tag-Name mit dem der Link umgeben werden soll, z.b. 'li', 'div'. Default: null
     * @param array  $sorroundAttributes Attribute die Umgebenden-Element hinzugefügt werden sollen. Default: array
     *
     * @return string
     */
    public function toLink(array $params = [], array $attributes = [], $sorroundTag = null, array $sorroundAttributes = [])
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
     *
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
                        $return[] = rex_category::get($var, $this->clang_id);
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Checks if $anObj is in the parent tree of the object.
     *
     * @param self $anObj
     *
     * @return bool
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
     * @return bool
     */
    public function isStartArticle()
    {
        return $this->startarticle;
    }

    /**
     * Returns true if this Article is the Startpage for the entire site.
     *
     * @return bool
     */
    public function isSiteStartArticle()
    {
        return $this->id == rex_article::getSiteStartArticleId();
    }

    /**
     * Returns  true if this Article is the not found article.
     *
     * @return bool
     */
    public function isNotFoundArticle()
    {
        return $this->id == rex_article::getNotfoundArticleId();
    }
}
