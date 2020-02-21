<?php

/**
 * @package redaxo\core\form
 */
class rex_form_container_element extends rex_form_element
{
    /** @array */
    private $fields;
    /** @var bool */
    private $multiple;
    /** @var string */
    private $active;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
        $this->fields = [];
        $this->multiple = true;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setMultiple($multiple = true)
    {
        $this->multiple = $multiple;
    }

    public function setActive($group)
    {
        $this->active = $group;
    }

    public function addField($type, $name, $value = null, array $attributes = [])
    {
        return $this->addGroupedField('elementContainer', $type, $name, $value, $attributes);
    }

    /**
     * @return rex_form_element
     */
    public function addGroupedField($group, $type, $name, $value = null, array $attributes = [])
    {
        $field = $this->table->createInput($type, $name, $value, $attributes);

        if (!isset($this->fields[$group])) {
            $this->fields[$group] = [];
        }

        $field->setAttribute('id', $this->getAttribute('id').'-'.$group.'-'.$field->getFieldName());
        $field->setAttribute('name', $this->getAttribute('name').'['.$group.']['.$field->getFieldName().']');
        $field->setValue($value);

        $this->fields[$group][] = $field;
        return $field;
    }

    public function getFields()
    {
        return $this->fields;
    }

    protected function prepareInnerFields()
    {
        $values = $this->getValue();
        if (is_string($values)) {
            $values = json_decode($values, true);
            if (!$this->multiple) {
                $values = [$this->active => $values];
            }
        }

        foreach ($this->fields as $group => $groupFields) {
            if (!$this->multiple && $this->active && $this->active !== $group) {
                continue;
            }

            foreach ($groupFields as $key => $field) {
                if (isset($values[$group][$field->getFieldName()])) {
                    $field->setValue($values[$group][$field->getFieldName()]);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function formatElement()
    {
        $this->prepareInnerFields();

        $attr = '';
        // Folgende attribute filtern:
        // - name: der container selbst ist kein feld, daher hat er keinen namen
        // - id:   eine id vergeben wir automatisiert pro gruppe
        $attributeFilter = ['id', 'name'];
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            if (in_array($attributeName, $attributeFilter)) {
                continue;
            }

            $attr .= ' ' . rex_escape($attributeName, 'html_attr') . '="' . rex_escape($attributeValue) . '"';
        }

        $format = '';
        foreach ($this->fields as $group => $groupFields) {
            $format .= '<div id="rex-' . rex_escape($group) . '"' . $attr . '>';
            foreach ($groupFields as $field) {
                $format .= $field->get();
            }
            $format .= '</div>';
        }
        return $format;
    }

    /**
     * @return string
     */
    protected function getFragment()
    {
        return 'core/form/container.php';
    }

    /**
     * @return string
     */
    public function getSaveValue()
    {
        $this->prepareInnerFields();

        $value = [];
        if ($this->multiple) {
            foreach ($this->fields as $group => $groupFields) {
                foreach ($groupFields as $field) {
                    // read-only-fields nicht speichern
                    if (false === strpos($field->getAttribute('class'), 'form-control-static')) {
                        $value[$group][$field->getFieldName()] = $field->getSaveValue();
                    }
                }
            }
        } elseif (isset($this->active) && isset($this->fields[$this->active])) {
            foreach ($this->fields[$this->active] as $field) {
                // read-only-fields nicht speichern
                if (false === strpos($field->getAttribute('class'), 'form-control-static')) {
                    $value[$field->getFieldName()] = $field->getSaveValue();
                }
            }
        }
        return json_encode($value);
    }
}
