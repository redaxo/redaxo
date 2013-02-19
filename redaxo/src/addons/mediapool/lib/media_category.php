<?php


/**
 * Object Oriented Framework: Bildet eine Kategorie im Medienpool ab
 * @package redaxo5
 */

class rex_media_category
{
    // id
    private $_id = '';
    // re_id
    private $_parent_id = '';

    // name
    private $_name = '';
    // path
    private $_path = '';

    // createdate
    private $_createdate = '';
    // updatedate
    private $_updatedate = '';

    // createuser
    private $_createuser = '';
    // updateuser
    private $_updateuser = '';

    // child categories
    private $_children = '';
    // files (media)
    private $_files = '';

    protected function __construct()
    {
    }

    /**
     * @param int $id
     * @return self
     */
    static public function getCategoryById($id)
    {
        $id = (int) $id;
        if (!is_numeric($id))
            return null;

        $cat_path = rex_path::addonCache('mediapool', $id . '.mcat');
        if (!file_exists($cat_path)) {
            rex_media_cache::generateCategory($id);
        }

        if (file_exists($cat_path)) {
            $cache = rex_file::getCache($cat_path);

            $cat = new self();

            $cat->_id = $cache['id'];
            $cat->_parent_id = $cache['re_id'];

            $cat->_name = $cache['name'];
            $cat->_path = $cache['path'];

            $cat->_createdate = $cache['createdate'];
            $cat->_updatedate = $cache['updatedate'];

            $cat->_createuser = $cache['createuser'];
            $cat->_updateuser = $cache['updateuser'];

            $cat->_children = null;
            $cat->_files = null;

            return $cat;
        }

        return null;
    }

    /**
     * @return self[]
     */
    static public function getRootCategories()
    {
        return self :: getChildrenById(0);
    }

    /**
     * @param int $id
     * @return self[]
     */
    static public function getChildrenById($id)
    {
        $id = (int) $id;

        if (!is_int($id))
            return array();

        $catlist = array();

        $catlist_path = rex_path::addonCache('mediapool', $id . '.mclist');
        if (!file_exists($catlist_path)) {
            rex_media_cache::generateCategoryList($id);
        }

        if (file_exists($catlist_path)) {
            $cache = rex_file::getCache($catlist_path);

            if (is_array($cache)) {
                foreach ($cache as $cat_id)
                    $catlist[] = self :: getCategoryById($cat_id);
            }
        }

        return $catlist;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return __CLASS__ . ', "' . $this->getId() . '", "' . $this->getName() . '"' . "<br/>\n";
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Returns the path ids of the category as an array
     *
     * @return int[]
     */
    public function getPathAsArray()
    {
        $p = explode('|', $this->_path);
        foreach ($p as $k => $v) {
            if ($v == '')
                unset($p[$k]);
            else
                $p[$k] = (int) $v;
        }

        return array_values($p);
    }

    /**
     * @return string
     */
    public function getUpdateUser()
    {
        return $this->_updateuser;
    }

    /**
     * @return int
     */
    public function getUpdateDate()
    {
        return $this->_updatedate;
    }

    /**
     * @return string
     */
    public function getCreateUser()
    {
        return $this->_createuser;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->_createdate;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->_parent_id;
    }

    /**
     * @return self
     */
    public function getParent()
    {
        return self :: getCategoryById($this->getParentId());
    }

    /**
     * Get an array of all parentCategories.
     * Returns an array of rex_media_category objects sorted by $prior.
     *
     * @return self[]
     */
    public function getParentTree()
    {
        $tree = array();
        if ($this->_path) {
            $explode = explode('|', $this->_path);
            if (is_array($explode)) {
                foreach ($explode as $var) {
                    if ($var != '') {
                        $tree[] = self :: getCategoryById($var);
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * Checks if $anObj is in the parent tree of the object
     *
     * @param self $anObj
     * @return bool
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
     * @return self[]
     */
    public function getChildren()
    {
        if ($this->_children === null) {
            $this->_children = self :: getChildrenById($this->getId());
        }

        return $this->_children;
    }

    /**
     * @return int
     */
    public function countChildren()
    {
        return count($this->getChildren());
    }

    /**
     * @return rex_media[]
     */
    public function getMedia()
    {
        if ($this->_files === null) {
            $this->_files = array();
            $id = $this->getId();

            $list_path = rex_path::addonCache('mediapool', $id . '.mlist');
            if (!file_exists($list_path)) {
                rex_media_cache::generateList($id);
            }

            if (file_exists($list_path)) {
                $cache = rex_file::getCache($list_path);

                if (is_array($cache)) {
                    foreach ($cache as $filename)
                        $this->_files[] = rex_media :: getMediaByFileName($filename);
                }
            }
        }

        return $this->_files;
    }

    /**
     * @return int
     */
    public function countMedia()
    {
        return count($this->getMedia());
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->_hide;
    }

    /**
     * @return bool
     */
    public function isRootCategory()
    {
        return $this->hasParent() === false;
    }

    /**
     * @param self|int $mediaCat
     * @return bool
     */
    public function isParent($mediaCat)
    {
        if (is_int($mediaCat)) {
            return $mediaCat == $this->getParentId();
        } elseif ($mediaCat instanceof self) {
            return $this->getParentId() == $mediaCat->getId();
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return $this->getParentId() != 0;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * @return bool
     */
    public function hasMedia()
    {
        return count($this->getMedia()) > 0;
    }

    /**
     * @return string
     */
    static public function _getTableName()
    {
        return rex::getTablePrefix() . 'media_category';
    }

    /**
     * @return bool Returns <code>true</code> on success or <code>false</code> on error
     */
    public function save()
    {
        $sql = rex_sql::factory();
        $sql->setTable($this->_getTableName());
        $sql->setValue('re_id', $this->getParentId());
        $sql->setValue('name', $this->getName());
        $sql->setValue('path', $this->getPath());
        $sql->setValue('hide', $this->isHidden());

        if ($this->getId() !== null) {
            $sql->addGlobalUpdateFields();
            $sql->setWhere(array('id' => $this->getId()));
            $success = $sql->update();
            if ($success)
                rex_media_cache::deleteCategory($this->getId());
            return $success;
        } else {
            $sql->addGlobalCreateFields();
            $success = $sql->insert();
            if ($success)
                rex_media_cache::deleteCategoryList($this->getParentId());
            return $success;
        }
    }

    /**
     * @param bool $recurse
     * @return bool Returns <code>true</code> on success or <code>false</code> on error
     */
    public function delete($recurse = false)
    {
        // Rekursiv l�schen?
        if (!$recurse && $this->hasChildren()) {
            return false;
        }

        if ($recurse) {
            $childs = $this->getChildren();
            foreach ($childs as $child) {
                if (!$child->delete($recurse)) return false;
            }
        }

        // Alle Dateien l�schen
        if ($this->hasMedia()) {
            $files = $this->getMedia();
            foreach ($files as $file) {
                if (!$file->delete()) return false;
            }
        }

        $qry = 'DELETE FROM ' . $this->_getTableName() . ' WHERE id = ' . $this->getId() . ' LIMIT 1';
        $sql = rex_sql::factory();
        // $sql->setDebug();
        $sql->setQuery($qry);

        rex_media_cache::deleteCategory($this->getId());
        rex_media_cache::deleteList($this->getId());

        return $sql->getRows() == 1;
    }
}
