<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_input_linklistbutton extends rex_input
{
    private $buttonId;
    private $categoryId;

    public function __construct()
    {
        parent::__construct();
        $this->buttonId = '';
        $this->categoryId = '';
    }

    public function setButtonId($buttonId)
    {
        $this->buttonId = $buttonId;
        $this->setAttribute('id', 'REX_LINKLIST_' . $buttonId);
    }

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

        $field = rex_var_linklist::getWidget($buttonId, $name, $value, ['category' => $category]);

        return $field;
    }
}
