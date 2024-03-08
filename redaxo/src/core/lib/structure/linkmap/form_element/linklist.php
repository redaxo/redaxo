<?php

use Redaxo\Core\Form\AbstractForm;
use Redaxo\Core\Form\Field\BaseField;

class rex_form_widget_linklist_element extends BaseField
{
    /** @var int */
    private $categoryId = 0;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    /**
     * @param string $tag
     * @param array<string, int|string> $attributes
     */
    public function __construct($tag = '', ?AbstractForm $form = null, array $attributes = [])
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
