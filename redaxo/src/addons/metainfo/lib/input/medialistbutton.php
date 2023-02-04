<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 *
 * @extends rex_input<string>
 */
class rex_input_medialistbutton extends rex_input
{
    private string $buttonId = '';
    private array $args = [];

    public function __construct()
    {
        parent::__construct();
        $this->buttonId = '';
    }

    /**
     * @param int $buttonId
     * @return void
     */
    public function setButtonId($buttonId)
    {
        $this->buttonId = 'METAINFO_'.$buttonId;
        $this->setAttribute('id', 'REX_MEDIALIST_' . $this->buttonId);
    }

    /**
     * @param int|null $categoryId
     * @return void
     */
    public function setCategoryId($categoryId)
    {
        $this->args['category'] = $categoryId;
    }

    /**
     * @param string $types
     * @return void
     */
    public function setTypes($types)
    {
        $this->args['types'] = $types;
    }

    /**
     * @param bool $preview
     * @return void
     */
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

        return rex_var_medialist::getWidget($buttonId, $name, $value, $args);
    }
}
