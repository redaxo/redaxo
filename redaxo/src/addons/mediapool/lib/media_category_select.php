<?php

/**
 * Class MediaKategorie Select.
 *
 * @package redaxo\mediapool
 */
class rex_media_category_select extends rex_select
{
    /**
     * @var bool
     */
    private $check_perms;

    /**
     * @var int|int[]|null
     */
    private $rootId;

    private $loaded = false;

    public function __construct($check_perms = true)
    {
        $this->check_perms = $check_perms;
        $this->rootId = null;

        parent::__construct();
    }

    /**
     * Kategorie-Id oder ein Array von Kategorie-Ids als Wurzelelemente der Select-Box.
     *
     * @param int|int[]|null $rootId Kategorie-Id oder Array von Kategorie-Ids zur Identifikation der Wurzelelemente
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
        if (!$this->check_perms ||
                $this->check_perms && rex::getUser()->getComplexPerm('media')->hasCategoryPerm($mediacat->getId())
        ) {
            $mid = $mediacat->getId();
            $mname = $mediacat->getName();

            $this->addOption($mname, $mid, $mid, $mediacat->getParentId());
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
