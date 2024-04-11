<?php

use Redaxo\Core\Core;
use Redaxo\Core\MediaPool\MediaCategory;

class rex_media_category_select extends rex_select
{
    /** @var bool */
    private $checkPerms;

    /** @var int|list<int>|null */
    private $rootId;

    /** @var bool */
    private $loaded = false;

    public function __construct($checkPerms = true)
    {
        $this->checkPerms = $checkPerms;

        parent::__construct();
    }

    /**
     * Kategorie-Id oder ein Array von Kategorie-Ids als Wurzelelemente der Select-Box.
     *
     * @param int|list<int>|null $rootId Kategorie-Id oder Array von Kategorie-Ids zur Identifikation der Wurzelelemente
     * @return void
     */
    public function setRootId($rootId)
    {
        $this->rootId = $rootId;
    }

    /**
     * @return void
     */
    protected function addCatOptions()
    {
        if (null !== $this->rootId) {
            if (is_array($this->rootId)) {
                foreach ($this->rootId as $rootId) {
                    if ($rootCat = MediaCategory::get($rootId)) {
                        $this->addCatOption($rootCat);
                    }
                }
            } else {
                if ($rootCat = MediaCategory::get($this->rootId)) {
                    $this->addCatOption($rootCat);
                }
            }
        } else {
            if ($rootCats = MediaCategory::getRootCategories()) {
                foreach ($rootCats as $rootCat) {
                    $this->addCatOption($rootCat);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function addCatOption(MediaCategory $mediacat, int $parentId = 0)
    {
        if (!$this->checkPerms || Core::requireUser()->getComplexPerm('media')->hasCategoryPerm($mediacat->getId())
        ) {
            $mid = $mediacat->getId();
            $mname = $mediacat->getName();

            $this->addOption($mname, $mid, $mid, $parentId);

            $parentId = $mediacat->getId();
        }

        foreach ($mediacat->getChildren() as $child) {
            $this->addCatOption($child, $parentId);
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
