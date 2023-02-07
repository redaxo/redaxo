<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 *
 * @extends rex_input<int>
 */
class rex_input_linkbutton extends rex_input
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
        $this->setAttribute('id', 'REX_LINK_' . $this->buttonId);
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
        $categoryId = $this->categoryId;
        $value = rex_escape($this->value);
        $name = $this->attributes['name'];

        return rex_var_link::getWidget($buttonId, $name, $value, ['category' => $categoryId]);
    }
}
