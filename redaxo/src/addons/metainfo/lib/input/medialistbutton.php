<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_input_medialistbutton extends rex_input
{
    private $buttonId;
    private $args = [];

    public function __construct()
    {
        parent::__construct();
        $this->buttonId = '';
    }

    public function setButtonId($buttonId)
    {
        $this->buttonId = $buttonId;
        $this->setAttribute('id', 'REX_MEDIALIST_' . $buttonId);
    }

    public function setCategoryId($categoryId)
    {
        $this->args['category'] = $categoryId;
    }

    public function setTypes($types)
    {
        $this->args['types'] = $types;
    }

    public function setPreview($preview = true)
    {
        $this->args['preview'] = $preview;
    }

    public function getHtml()
    {
        $buttonId = $this->buttonId;
        $value = rex_escape($this->value);
        $name = $this->attributes['name'];
        $args = $this->args;

        $field = rex_var_medialist::getWidget($buttonId, $name, $value, $args);

        return $field;
    }
}
