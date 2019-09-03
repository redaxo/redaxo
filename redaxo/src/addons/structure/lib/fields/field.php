<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2019 studio ahoi
 */

class rex_structure_field extends rex_structure_field_base
{
    /**
     * @var static[]|string[]
     */
    protected $fields = [];
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * @var array
     */
    protected $vars = [];
    /**
     * @var string
     */
    protected $fragment_file;
    /**
     * @var mixed
     */
    protected $condition;

    /**
     * @param static|string $field
     *
     * @return $this
     */
    public function setField($field)
    {
        $this->fields[$field->getId()] = $field;

        return $this;
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
     * @param array $fields
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->fields[$field->getId()] = $field;
        }

        return $this;
    }

    /**
     * @param string $fragment_file
     *
     * @return $this
     */
    public function setFragment($fragment_file)
    {
        $this->fragment_file = $fragment_file;

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param array $vars
     *
     * @return $this
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;

        return $this;
    }

    /**
     * @param $condition
     *
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return string
     */
    public function get()
    {
        $return = '';

        foreach ($this->fields as $field) {
            if (isset($this->condition) && !$this->condition) {
                continue;
            }

            if ($field instanceof rex_structure_field_base) {
                $return .= $field->get();
            } else {
                $return .= $field;
            }
        }

        if ($this->fragment_file) {
            $fragment = new rex_fragment($this->vars + [
                'attributes' => $this->attributes,
                'fields' => $return,
            ]);

            $return = $fragment->parse($this->fragment_file);
        }

        return $return;
    }
}
