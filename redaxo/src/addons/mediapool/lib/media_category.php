<?php

/**
 * Object Oriented Framework: Bildet eine Kategorie im Medienpool ab
 * @package redaxo\mediapool
 */
class rex_media_category
{
    use rex_instance_pool_trait;
    use rex_instance_list_pool_trait;

    // id
    private $id = '';
    // parent_id
    private $parent_id = '';

    // name
    private $name = '';
    // path
    private $path = '';

    // createdate
    private $createdate = '';
    // updatedate
    private $updatedate = '';

    // createuser
    private $createuser = '';
    // updateuser
    private $updateuser = '';

    /**
     * @param int $id
     * @return self
     */
    public static function get($id)
    {
        $id = (int) $id;

        if (0 >= $id) {
            return null;
        }

        return self::getInstance($id, function ($id) {
            $cat_path = rex_path::addonCache('mediapool', $id . '.mcat');
            if (!file_exists($cat_path)) {
                rex_media_cache::generateCategory($id);
            }

            if (file_exists($cat_path)) {
                $cache = rex_file::getCache($cat_path);

                $cat = new self();

                $cat->id = $cache['id'];
                $cat->parent_id = $cache['parent_id'];

                $cat->name = $cache['name'];
                $cat->path = $cache['path'];

                $cat->createdate = $cache['createdate'];
                $cat->updatedate = $cache['updatedate'];

                $cat->createuser = $cache['createuser'];
                $cat->updateuser = $cache['updateuser'];

                $cat->children = null;
                $cat->files = null;

                return $cat;
            }

            return null;
        });
    }

    /**
     * @return self[]
     */
    public static function getRootCategories()
    {
        return self::getChildCategories(0);
    }

    /**
     * @param int $parentId
     * @return self[]
     */
    protected static function getChildCategories($parentId)
    {
        $parentId = (int) $parentId;
        if (0 > $parentId) {
            return [];
        }

        return self::getInstanceList([$parentId, 'children'], 'self::get', function ($parentId) {
            $catlist_path = rex_path::addonCache('mediapool', $parentId . '.mclist');
            if (!file_exists($catlist_path)) {
                rex_media_cache::generateCategoryList($parentId);
            }
            return rex_file::getCache($catlist_path);
        });
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the path ids of the category as an array
     *
     * @return int[]
     */
    public function getPathAsArray()
    {
        $p = explode('|', $this->path);
        foreach ($p as $k => $v) {
            if ($v == '') {
                unset($p[$k]);
            } else {
                $p[$k] = (int) $v;
            }
        }

        return array_values($p);
    }

    /**
     * @return string
     */
    public function getUpdateUser()
    {
        return $this->updateuser;
    }

    /**
     * @return int
     */
    public function getUpdateDate()
    {
        return $this->updatedate;
    }

    /**
     * @return string
     */
    public function getCreateUser()
    {
        return $this->createuser;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->createdate;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @return self
     */
    public function getParent()
    {
        return self::get($this->getParentId());
    }

    /**
     * Get an array of all parentCategories.
     * Returns an array of rex_media_category objects sorted by $priority.
     *
     * @return self[]
     */
    public function getParentTree()
    {
        $tree = [];
        if ($this->path) {
            $explode = explode('|', $this->path);
            if (is_array($explode)) {
                foreach ($explode as $var) {
                    if ($var != '') {
                        $tree[] = self::get($var);
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
        return self::getChildCategories($this->getId());
    }

    /**
     * @return rex_media[]
     */
    public function getMedia()
    {
        return self::getInstanceList([$this->getId(), 'media'], 'rex_media::get', function ($id) {
            $list_path = rex_path::addonCache('mediapool', $id . '.mlist');
            if (!file_exists($list_path)) {
                rex_media_cache::generateList($id);
            }
            return rex_file::getCache($list_path);
        });
    }

    /**
     * @param self $mediaCat
     * @return bool
     */
    public function isParent(self $mediaCat)
    {
        return $this->getParentId() == $mediaCat->getId();
    }

    /**
     * @return string
     */
    public static function _getTableName()
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
        $sql->setValue('parent_id', $this->getParentId());
        $sql->setValue('name', $this->getName());
        $sql->setValue('path', $this->getPath());
        $sql->setValue('hide', $this->isHidden());

        if ($this->getId() !== null) {
            try {
                $sql->addGlobalUpdateFields();
                $sql->setWhere(['id' => $this->getId()]);
                $sql->update();
                rex_media_cache::deleteCategory($this->getId());
                return true;
            } catch (rex_sql_exception $e) {
                return false;
            }
        } else {
            try {
                $sql->addGlobalCreateFields();
                $sql->insert();
                rex_media_cache::deleteCategoryList($this->getParentId());
                return true;
            } catch (rex_sql_exception $e) {
                return false;
            }
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

        if ($children = $this->getChildren()) {
            foreach ($children as $child) {
                if (!$child->delete($recurse)) {
                    return false;
                }
            }
        }

        // Alle Dateien l�schen
        if ($files = $this->getMedia()) {
            foreach ($files as $file) {
                if (!$file->delete()) {
                    return false;
                }
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
