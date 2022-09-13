<?php

/**
 * @package redaxo\mediapool
 */
class rex_form_widget_medialist_element extends rex_form_element
{
    private $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    /**
     * @param string $tag
     * @param array<string, int|string> $attributes
     */
    public function __construct($tag = '', rex_form_base $form = null, array $attributes = [])
    {
        parent::__construct('', $form, $attributes);
    }

    /**
     * @return void
     */
    public function setCategoryId($categoryId)
    {
        $this->args['category'] = $categoryId;
    }

    /**
     * @return void
     */
    public function setTypes($types)
    {
        $this->args['types'] = $types;
    }

    /**
     * @return void
     */
    public function setPreview($preview = true)
    {
        $this->args['preview'] = $preview;
    }

    public function formatElement()
    {
        static $widgetCounter = 1;

        $html = rex_var_medialist::getWidget($widgetCounter, $this->getAttribute('name'), $this->getValue(), $this->args);

        ++$widgetCounter;
        return $html;
    }
}
