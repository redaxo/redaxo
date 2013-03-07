<?php

/**
 * Object Oriented Framework: Basisklasse für die Strukturkomponenten
 *
 * @package redaxo\structure
 */
abstract class rex_structure_element
{
    /*
     * these vars get read out
     */
    protected
        $_id = '',
        $_re_id = '',
        $_clang = '',
        $_name = '',
        $_catname = '',
        $_template_id = '',
        $_path = '',
        $_prior = '',
        $_startarticle = '',
        $_status = '',
        $_updatedate = '',
        $_createdate = '',
        $_updateuser = '',
        $_createuser = '';

    /**
     * Constructor
     *
     * @param bool|array $params
     * @param bool|int   $clang
     */
    protected function __construct($params = false, $clang = false)
    {
        if ($params !== false) {
            foreach (self :: getClassVars() as $var) {
                if (isset($params[$var])) {
                    $class_var = '_' . $var;
                    $this->$class_var = $params[$var];
                }
            }
        }

        if ($clang !== false) {
            $this->setClang($clang);
        }
    }

    /**
     * @param int $clang
     */
    public function setClang($clang)
    {
        $this->_clang = $clang;
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
        foreach (['_', 'art_', 'cat_'] as $prefix) {
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
            $values = self :: getClassVars();
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
            $file = rex_path::addonCache('structure',  $startId . '.0.article');
            if (!rex::isBackend() && file_exists($file)) {
                // da getClassVars() eine statische Methode ist, können wir hier nicht mit $this->getId() arbeiten!
                $genVars = self::convertGeneratedArray(rex_file::getCache($file), 0);
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
    public static function convertGeneratedArray(array $generatedArray, $clang)
    {
        $rex_structure_elementArray['id'] = $generatedArray['article_id'][$clang];
        $rex_structure_elementArray['clang'] = $clang;
        foreach ($generatedArray as $key => $var) {
            $rex_structure_elementArray[$key] = $var[$clang];
        }
        unset ($rex_structure_elementArray['_article_id']);
        return $rex_structure_elementArray;
    }

    /**
     * Array of rex_structure_element instances, keyed by classname, id and clang
     * @var self[][][]
     */
    private static $instanceCache = [];

    /**
     * Return an rex_structure_element object based on an id.
     * The instance will be cached in an instance-pool and therefore re-used by a later call.
     *
     * @param int $id    the article id
     * @param int $clang the clang id
     * @throws rex_exception
     * @return rex_structure_element A rex_structure_element instance typed to the late-static binding type of the caller
     */
    protected static function getById($id, $clang)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        // save cache per subclass
        $subclass = get_called_class();

        // check if the class was already stored in the instanceCache
        if (isset(self::$instanceCache[$subclass][$id][$clang])) {
            return self::$instanceCache[$subclass][$id][$clang];
        }

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
            $impl = new $subclass(self :: convertGeneratedArray($metadata, $clang));

            // put the constructed object into the instance-cache for faster re-use
            self::$instanceCache[$subclass][$id][$clang] = $impl;
            return $impl;
        }

        return null;
    }

    /**
     * Returns the clang of the category
     *
     * @return integer
     */
    public function getClang()
    {
        return $this->_clang;
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
        return $this->_id;
    }

    /**
     * Returns the parent_id of the article
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->_re_id;
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
     * @param bool|int $clang
     * @return self
     */
    abstract public function getParent($clang = false);

    /**
     * Returns the name of the article
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the article priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->_prior;
    }

    /**
     * Returns the last update user
     *
     * @return string
     */
    public function getUpdateUser()
    {
        return $this->_updateuser;
    }

    /**
     * Returns the last update date
     *
     * @param array $format
     * @return integer
     */
    public function getUpdateDate($format = null)
    {
        return self :: _getDate($this->_updatedate, $format);
    }

    /**
     * Returns the creator
     *
     * @return string
     */
    public function getCreateUser()
    {
        return $this->_createuser;
    }

    /**
     * Returns the creation date
     *
     * @param array $format
     * @return integer
     */
    public function getCreateDate($format = null)
    {
        return self :: _getDate($this->_createdate, $format);
    }

    /**
     * Returns true if article is online.
     *
     * @return boolean
     */
    public function isOnline()
    {
        return $this->_status == 1;
    }

    /**
     * Returns true if article is offline.
     *
     * @return boolean
     */
    public function isOffline()
    {
        return $this->_status == 0;
    }

    /**
     * Returns the template id
     *
     * @return integer
     */
    public function getTemplateId()
    {
        return $this->_template_id;
    }

    /**
     * Returns true if article has a template.
     *
     * @return boolean
     */
    public function hasTemplate()
    {
        return $this->_template_id > 0;
    }

    /**
     * Returns a link to this article
     *
     * @param array|string $params              Parameter für den Link
     * @param array        $attributes          Attribute die dem Link hinzugefügt werden sollen. Default: null
     * @param string       $sorround_tag        HTML-Tag-Name mit dem der Link umgeben werden soll, z.b. 'li', 'div'. Default: null
     * @param array        $sorround_attributes Attribute die Umgebenden-Element hinzugefügt werden sollen. Default: null
     * @return string
     */
    public function toLink($params = '', array $attributes = null, $sorround_tag = null, array $sorround_attributes = null)
    {
        $name = htmlspecialchars($this->getName());
        $link = '<a href="' . $this->getUrl($params) . '"' . $this->_toAttributeString($attributes) . ' title="' . $name . '">' . $name . '</a>';

        if ($sorround_tag !== null && is_string($sorround_tag)) {
            $link = '<' . $sorround_tag . $this->_toAttributeString($sorround_attributes) . '>' . $link . '</' . $sorround_tag . '>';
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

        if ($this->_path) {
            if ($this->isStartArticle())
            $explode = explode('|', $this->_path . $this->_id . '|');
            else
            $explode = explode('|', $this->_path);

            if (is_array($explode)) {
                foreach ($explode as $var) {
                    if ($var != '') {
                        $return[] = rex_category :: getCategoryById($var, $this->_clang);
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
    public function inParentTree($anObj)
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
        return $this->_startarticle;
    }

    /**
     * Returns true if this Article is the Startpage for the entire site.
     *
     * @return boolean
     */
    public function isSiteStartArticle()
    {
        return $this->_id == rex::getProperty('start_article_id');
    }

    /**
     * Returns  true if this Article is the not found article
     *
     * @return boolean
     */
    public function isNotFoundArticle()
    {
        return $this->_id == rex::getProperty('notfound_article_id');
    }

    /**
     * Returns a String representation of this object
     * for debugging purposes.
     *
     * @return string
     */
    public function toString()
    {
        return $this->_id . ', ' . $this->_name . ', ' . ($this->isOnline() ? 'online' : 'offline');
    }

    /**
     * Formats a datestamp with the given format.
     *
     * If format is <code>null</code> the datestamp is returned.
     *
     * If format is <code>''</code> the datestamp is formated
     * with the default <code>dateformat</code> (lang-files).
     */
    protected static function _getDate($date, $format = null)
    {
        if ($format !== null) {
            if ($format == '') {
                $format = rex_i18n::msg('dateformat');
            }
            return strftime($format, $date);
        }
        return $date;
    }
}
