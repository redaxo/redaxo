<?php

/**
 * @package redaxo\mediapool
 */
class rex_form_widget_media_element extends rex_form_element
{
    private $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
    }

    public function setCategoryId($category_id)
    {
        $this->args['category'] = $category_id;
    }

    public function setTypes($types)
    {
        $this->args['types'] = $types;
    }

    public function setPreview($preview = true)
    {
        $this->args['preview'] = $preview;
    }

    public function formatElement()
    {
        static $widget_counter = 1;

        $html = rex_var_media::getWidget($widget_counter, $this->getAttribute('name'), $this->getValue(), $this->args);

        ++$widget_counter;
        return $html;
    }
}
