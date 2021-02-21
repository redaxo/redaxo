<?php

/**
 * @package redaxo\structure
 */
class rex_form_widget_linkmap_element extends rex_form_element
{
    private $category_id = 0;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstruktorparameter
    public function __construct($tag = '', rex_form_base $form = null, array $attributes = [])
    {
        parent::__construct('', $form, $attributes);
    }

    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;
    }

    public function formatElement()
    {
        static $widgetCounter = 1;

        $html = rex_var_link::getWidget($widgetCounter, $this->getAttribute('name'), $this->getValue(), ['category' => $this->category_id]);

        ++$widgetCounter;
        return $html;
    }
}
