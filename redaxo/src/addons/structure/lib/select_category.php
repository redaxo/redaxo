<?php

/**
 * @package redaxo\structure
 */
class rex_category_select extends rex_select
{
    private $ignore_offlines;
    private $clang;
    private $check_perms;

    /**
     * @var int
     */
    private $rootId;

    private $loaded;

    public function __construct($ignore_offlines = false, $clang = false, $check_perms = true, $add_homepage = true)
    {
        $this->ignore_offlines = $ignore_offlines;
        $this->clang = $clang;
        $this->check_perms = $check_perms;
        $this->add_homepage = $add_homepage;
        $this->rootId = null;
        $this->loaded = false;

        parent::__construct();
    }

    /**
     * Kategorie-Id oder ein Array von Kategorie-Ids als Wurzelelemente der Select-Box.
     *
     * @param mixed $rootId Kategorie-Id oder Array von Kategorie-Ids zur Identifikation der Wurzelelemente.
     */
    public function setRootId($rootId)
    {
        $this->rootId = $rootId;
    }

    protected function addCatOptions()
    {
        if ($this->add_homepage)
            $this->addOption('Homepage', 0);

        if ($this->rootId !== null) {
            if (is_array($this->rootId)) {
                foreach ($this->rootId as $rootId) {
                    if ($rootCat = rex_category::getCategoryById($rootId, $this->clang)) {
                        $this->addCatOption($rootCat, 0);
                    }
                }
            } else {
                if ($rootCat = rex_category::getCategoryById($this->rootId, $this->clang)) {
                    $this->addCatOption($rootCat, 0);
                }
            }
        } else {
            if (!$this->check_perms || rex::getUser()->getComplexPerm('structure')->hasCategoryPerm(0)) {
                if ($rootCats = rex_category :: getRootCategories($this->ignore_offlines, $this->clang)) {
                    foreach ($rootCats as $rootCat) {
                        $this->addCatOption($rootCat);
                    }
                }
            } elseif (rex::getUser()->getComplexPerm('structure')->hasMountpoints()) {
                $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
                foreach ($mountpoints as $id) {
                    $cat = rex_category::getCategoryById($id, $this->clang);
                    if ($cat && !rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($cat->getParentId()))
                        $this->addCatOption($cat, 0);
                }
            }
        }
    }

    protected function addCatOption(rex_category $cat, $group = null)
    {
        if (!$this->check_perms ||
                $this->check_perms && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($cat->getId(), false)
        ) {
            $cid = $cat->getId();
            $cname = $cat->getName();

            if (rex::getUser()->hasPerm('advancedMode[]'))
                $cname .= ' [' . $cid . ']';

            if ($group === null)
                $group = $cat->getParentId();

            $this->addOption($cname, $cid, $cid, $group);
            $childs = $cat->getChildren($this->ignore_offlines, $this->clang);
            if (is_array($childs)) {
                foreach ($childs as $child) {
                    $this->addCatOption($child);
                }
            }
        }
    }

    public function get()
    {
        if (!$this->loaded) {
            $this->addCatOptions();
            $this->loaded = true;
        }

        return parent::get();
    }

    protected function outGroup($re_id, $level = 0, $optgroups = false)
    {
        if ($level > 100) {
            // nur mal so zu sicherheit .. man weiss nie ;)
            throw new rex_exception('select->_outGroup overflow');
        }

        $ausgabe = '';
        $group = $this->getGroup($re_id);
        if (!is_array($group)) {
            return '';
        }
        foreach ($group as $option) {
            $name = $option[0];
            $value = $option[1];
            $id = $option[2];
            if ($id == 0 || !$this->check_perms || ($this->check_perms && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($option[2]))) {
                    $ausgabe .= $this->outOption($name, $value, $level);
            } elseif (($this->check_perms && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($option[2]))) {
                $level--;
            }

            $subgroup = $this->getGroup($id, true);
            if ($subgroup !== false) {
                $ausgabe .= $this->outGroup($id, $level + 1);
            }
        }
        return $ausgabe;
    }

}
