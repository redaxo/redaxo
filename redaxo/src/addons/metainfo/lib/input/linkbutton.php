<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_input_linkbutton extends rex_input
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
        $this->setAttribute('id', 'LINK_' . $buttonId);
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getHtml()
    {
        $buttonId = $this->buttonId;
        $categoryId = $this->categoryId;
        $value = rex_escape($this->value);
        $name = $this->attributes['name'];

        $field = rex_var_link::getWidget($buttonId, $name, $value, ['category' => $categoryId]);

        return $field;
    }
}
