<?php

use Redaxo\Core\Form\AbstractForm;
use Redaxo\Core\Form\Field\BaseField;

class rex_form_widget_medialist_element extends BaseField
{
    /** @var array{category?: int, types?: string, preview?: bool} */
    private $args = [];

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
     *
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
     *
     * @return void
     */
    public function setPreview($preview = true)
    {
        $this->args['preview'] = $preview;
    }

    public function formatElement()
    {
        /** @var int $widgetCounter */
        static $widgetCounter = 1;

        $html = rex_var_medialist::getWidget($widgetCounter, $this->getAttribute('name'), $this->getValue(), $this->args);

        ++$widgetCounter;
        return $html;
    }
}
