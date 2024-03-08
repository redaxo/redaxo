<?php

namespace Redaxo\Core\Form\Field;

use rex_complex_perm;

/**
 * @internal
 */
class PermissionSelectField extends SelectField
{
    protected string $checkboxLabel = 'All';

    public function getSaveValue()
    {
        $value = $this->getValue();

        if ($value && str_contains($value, '|' . rex_complex_perm::ALL . '|')) {
            return rex_complex_perm::ALL;
        }

        return $value;
    }

    /**
     * @param string $label
     * @return void
     */
    public function setCheckboxLabel($label)
    {
        $this->checkboxLabel = $label;
    }

    /**
     * @return string
     */
    public function get()
    {
        $field = new CheckboxField('', $this->table);
        $field->setAttribute('name', $this->getAttribute('name', ''));
        $field->setAttribute('id', $this->getAttribute('id', ''));
        if (rex_complex_perm::ALL == trim((string) $this->getValue(), '|')) {
            $field->setValue('|' . rex_complex_perm::ALL . '|');
        }
        $field->addOption($this->checkboxLabel, rex_complex_perm::ALL);
        $this->setAttribute('class', 'form-control');
        return $field->get() . parent::get();
    }
}
