<?php
/**
 * Class representing a general page element
 * implements composite pattern to render all sub elements
 */

class rex_structure_field_group extends rex_structure_field_base
{
    /**
     * @var array
     */
    protected $fields = [];
    /**
     * @var rex_fragment
     */
    protected $fragment;

    /**
     * @param rex_structure_field_base|rex_structure_field_base[] $field
     *
     * @return $this
     */
    public function setField($field)
    {
        if (is_array($field)) {
            foreach ($field as $f) {
                if ($f instanceof rex_structure_field_base) {
                    $this->fields[$f->getId()] = $f;
                }
            }
        } elseif ($field instanceof rex_structure_field_base) {
            $this->fields[$field->getId()] = $field;
        } else {
            throw new InvalidArgumentException(sprintf('Expecting $field to be instance of '.rex_structure_field_base::class.', %s given!', gettype($field)));
        }

        return $this;
    }

    /**
     * @param string $field_id
     *
     * @return $this
     */
    public function getField($field_id)
    {
        return $this->fields[$field_id];
    }

    /**
     * @param string $field_id
     *
     * @return $this
     */
    public function unsetField($field_id)
    {
        unset($this->fields[$field_id]);

        return $this;
    }

    /**
     * @param rex_fragment $fragment
     *
     * @return $this
     */
    public function setFragment(rex_fragment $fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * @return string
     */
    public function get()
    {
        if (!$this->isCondition()) {
            return '';
        }

        $return = '';
        foreach ($this->fields as $field) {
            if ($field instanceof rex_structure_field_base) {
                $return .= $field
                    ->setContext($this->context, $this->sql)
                    ->setType($this->type)
                    ->get();
            }
        }

        if (!$this->fragment instanceof rex_fragment) {
            return $return;
        }

        return $this->fragment
            ->setVar('type', $this->type, false)
            ->setVar('fields', $return, false)
            ->parse();
    }
}
