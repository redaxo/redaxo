<?php

/**
 * @package redaxo\structure
 */
class rex_form_widget_linklist_element extends rex_form_element
{
    /** @var int */
    private $categoryId = 0;

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
     * @param int $categoryId
     * @return void
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function formatElement()
    {
        /** @var int $widgetCounter */
        static $widgetCounter = 1;

        $html = rex_var_linklist::getWidget($widgetCounter, $this->getAttribute('name'), $this->getValue(), ['category' => $this->categoryId]);

        ++$widgetCounter;
        return $html;
    }
}
