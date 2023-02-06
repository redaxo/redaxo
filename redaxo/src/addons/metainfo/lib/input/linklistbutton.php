<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 *
 * @extends rex_input<string>
 */
class rex_input_linklistbutton extends rex_input
{
    private string $buttonId = '';
    private ?int $categoryId = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $buttonId
     * @return void
     */
    public function setButtonId($buttonId)
    {
        $this->buttonId = 'METAINFO_'.$buttonId;
        $this->setAttribute('id', 'REX_LINKLIST_' . $this->buttonId);
    }

    /**
     * @param int|null $categoryId
     * @return void
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getHtml()
    {
        $buttonId = $this->buttonId;
        $category = $this->categoryId;
        $value = rex_escape($this->value);
        $name = $this->attributes['name'];

        return rex_var_linklist::getWidget($buttonId, $name, $value, ['category' => $category]);
    }
}
