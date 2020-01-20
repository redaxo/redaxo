<?php

/**
 * Class MediaKategorie Select.
 *
 * @package redaxo\mediapool
 */
class rex_media_category_select extends rex_select
{
    private $check_perms;

    private $check_read_perms;

    /**
     * @var int|int[]
     */
    private $rootId;

    private $loaded = false;

    public function __construct($check_perms = true, $check_read_perms = false)
    {
        $this->check_perms = $check_perms;
        $this->check_read_perms = $check_read_perms;
        if ($check_perms === false && $check_read_perms === true) {
            $this->check_perms = true;
        }
        $this->rootId = null;

        parent::__construct();
    }

    /**
     * Kategorie-Id oder ein Array von Kategorie-Ids als Wurzelelemente der Select-Box.
     *
     * @param mixed $rootId Kategorie-Id oder Array von Kategorie-Ids zur Identifikation der Wurzelelemente
     */
    public function setRootId($rootId)
    {
        $this->rootId = $rootId;
    }

    protected function addCatOptions()
    {
        if (null !== $this->rootId) {
            if (is_array($this->rootId)) {
                foreach ($this->rootId as $rootId) {
                    if ($rootCat = rex_media_category::get($rootId)) {
                        $this->addCatOption($rootCat);
                    }
                }
            } else {
                if ($rootCat = rex_media_category::get($this->rootId)) {
                    $this->addCatOption($rootCat);
                }
            }
        } else {
            if ($rootCats = rex_media_category::getRootCategories()) {
                foreach ($rootCats as $rootCat) {
                    $this->addCatOption($rootCat);
                }
            }
        }
    }

    protected function addCatOption(rex_media_category $mediacat)
    {
        $childWithPermission = false;
        $parentWithPermission = false;

        if (rex::getUser()->getComplexPerm('media')->hasAll()) {
            $this->check_perms = false;
        }

        if ($this->check_perms) {
            $childWithPermission = media_category_perm_helper::checkChildren($mediacat, $this->check_read_perms);
            $parentWithPermission = media_category_perm_helper::checkParents($mediacat, $this->check_read_perms);
        }

        if (!$this->check_perms ||
            $this->check_perms && (
                rex::getUser()->getComplexPerm('media')->hasCategoryPerm($mediacat->getId()) // check media cat
                || ($this->check_read_perms && rex::getUser()->getComplexPerm('media_read')->hasCategoryPerm($mediacat->getId()))
                || $parentWithPermission instanceof rex_media_category // check all parents
                || $childWithPermission instanceof rex_media_category // check children
            )
        ) {
            $categoryId = $mediacat->getId();
            $parentCategoryId = $mediacat->getParentId();
            $value = $categoryId;
            $attributes = array();

            // no permission for parent set as id for parent the id from the first child with permission
            if ($this->check_perms && $childWithPermission instanceof rex_media_category && (
                    $value != $childWithPermission->getId() // my id is not the id of the child with the permission
                    && (
                        media_category_perm_helper::isIdParentInPath($childWithPermission, $value) === true // and my id is in the path
                        && media_category_perm_helper::isIdParentInPath($mediacat, $childWithPermission->getId()) !== true // and the child id is not in my path!
                    )
                )
            ) {
                $value = $childWithPermission->getId();
                $attributes['disabled'] = '1';
            }

            $categoryName = $mediacat->getName() . ' [' . $categoryId . ']';
            $this->addOption($categoryName, $value, $categoryId, $parentCategoryId, $attributes);

            $childs = $mediacat->getChildren();

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
}
