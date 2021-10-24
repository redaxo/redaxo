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
        $this->buttonId = 'METAINFO_'.$buttonId;
        $this->setAttribute('id', 'REX_LINK_' . $this->buttonId);
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

        return rex_var_link::getWidget($buttonId, $name, $value, ['category' => $categoryId]);
    }
}
