<?php

/**
 * @package redaxo\core\form
 */
class rex_form_select_element extends rex_form_element
{
    /** @var rex_select */
    protected $select;
    /** @var string */
    private $separator;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);

        $this->select = new rex_select();
        $this->separator = '|';
    }

    public function formatElement()
    {
        $multipleSelect = false;

        // Hier die Attribute des Elements an den Select weitergeben, damit diese angezeigt werden
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            $this->select->setAttribute($attributeName, $attributeValue);
        }

        if ($this->select->hasAttribute('multiple')) {
            $multipleSelect = true;
        }

        if ($multipleSelect) {
            $this->setAttribute('name', $this->getAttribute('name') . '[]');

            $selectedOptions = explode($this->separator, trim($this->getValue(), $this->separator));
            if (is_array($selectedOptions) && '' != $selectedOptions[0]) {
                foreach ($selectedOptions as $selectedOption) {
                    $this->select->setSelected($selectedOption);
                }
            }
        } else {
            $this->select->setSelected($this->getValue());
        }

        $this->select->setName($this->getAttribute('name'));
        return $this->select->get();
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * @return rex_select
     */
    public function getSelect()
    {
        return $this->select;
    }

    public function setSelect(rex_select $selectObj)
    {
        $this->select = $selectObj;
        if ($selectObj->hasAttribute('multiple')) {
            $this->setAttribute('multiple', $selectObj->getAttribute('multiple'));
        }
    }
}
