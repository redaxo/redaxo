<?php

/**
 * @package redaxo\core
 */
class rex_select
{
    private $attributes = [];
    private $currentOptgroup = 0;
    private $optgroups = [];
    private $options = [];
    private $option_selected;
    private $optCount = 0;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->resetSelected();
        $this->setName('standard');
        $this->setSize('1');
        $this->setMultiple(false);
        $this->setDisabled(false);
    }

    public function setAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function delAttribute($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->attributes[$name]);
            return true;
        }
        return false;
    }

    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    public function getAttribute($name, $default = '')
    {
        if ($this->hasAttribute($name)) {
            return $this->attributes[$name];
        }
        return $default;
    }

    public function setMultiple($multiple = true)
    {
        if ($multiple) {
            $this->setAttribute('multiple', 'multiple');
            if ($this->getAttribute('size') == '1') {
                $this->setSize('5');
            }
        } else {
            $this->delAttribute('multiple');
        }
    }

    public function setDisabled($disabled = true)
    {
        if ($disabled) {
            $this->setAttribute('disabled', 'disabled');
        } else {
            $this->delAttribute('disabled');
        }
    }

    public function setName($name)
    {
        $this->setAttribute('name', $name);
    }

    public function setId($id)
    {
        $this->setAttribute('id', $id);
    }

    /**
     * select style
     * Es ist moeglich sowohl eine Styleklasse als auch einen Style zu uebergeben.
     *
     * Aufrufbeispiel:
     * $sel_media->setStyle('class="inp100"');
     * und/oder
     * $sel_media->setStyle("width:150px;");
     */
    public function setStyle($style)
    {
        if (strpos($style, 'class=') !== false) {
            if (preg_match('/class=["\']?([^"\']*)["\']?/i', $style, $matches)) {
                $this->setAttribute('class', $matches[1]);
            }
        } else {
            $this->setAttribute('style', $style);
        }
    }

    public function setSize($size)
    {
        $this->setAttribute('size', $size);
    }

    public function setSelected($selected)
    {
        if (is_array($selected)) {
            foreach ($selected as $sectvalue) {
                $this->setSelected($sectvalue);
            }
        } else {
            $this->option_selected[] = $selected;
        }
    }

    public function resetSelected()
    {
        $this->option_selected = [];
    }

    public function addOptgroup($label)
    {
        ++$this->currentOptgroup;
        $this->optgroups[$this->currentOptgroup] = $label;
    }

    /**
     * Fügt eine Option hinzu.
     */
    public function addOption($name, $value, $id = 0, $parent_id = 0, array $attributes = [])
    {
        $this->options[$this->currentOptgroup][$parent_id][] = [$name, $value, $id, $attributes];
        ++$this->optCount;
    }

    /**
     * Fügt ein Array von Optionen hinzu, dass eine mehrdimensionale Struktur hat.
     *
     * Dim   Wert
     * 0.    Name
     * 1.    Value
     * 2.    Id
     * 3.    parent_id
     * 4.    Selected
     * 5.    Attributes
     */
    public function addOptions($options, $useOnlyValues = false)
    {
        if (is_array($options) && count($options) > 0) {
            // Hier vorher auf is_array abfragen, da bei Strings auch die Syntax mit [] funktioniert
            // $ab = "hallo"; $ab[2] -> "l"
            $grouped = isset($options[0]) && is_array($options[0]) && isset($options[0][2]) && isset($options[0][3]);
            foreach ($options as $key => $option) {
                $option = (array) $option;
                $attributes = [];
                if (isset($option[5]) && is_array($option[5])) {
                    $attributes = $option[5];
                }
                if ($grouped) {
                    $this->addOption($option[0], $option[1], $option[2], $option[3], $attributes);
                    if (isset($option[4]) && $option[4]) {
                        $this->setSelected($option[1]);
                    }
                } else {
                    if ($useOnlyValues) {
                        $this->addOption($option[0], $option[0]);
                    } else {
                        if (!isset($option[1])) {
                            $option[1] = $key;
                        }

                        $this->addOption($option[0], $option[1]);
                    }
                }
            }
        }
    }

    /**
     * Fügt ein Array von Optionen hinzu, dass eine Key/Value Struktur hat.
     * Wenn $use_keys mit false, werden die Array-Keys mit den Array-Values überschrieben.
     */
    public function addArrayOptions(array $options, $use_keys = true)
    {
        foreach ($options as $key => $value) {
            if (!$use_keys) {
                $key = $value;
            }

            $this->addOption($value, $key);
        }
    }

    public function countOptions()
    {
        return $this->optCount;
    }

    /**
     * Fügt Optionen anhand der Übergeben SQL-Select-Abfrage hinzu.
     */
    public function addSqlOptions($qry)
    {
        $sql = rex_sql::factory();
        $this->addOptions($sql->getArray($qry, [], PDO::FETCH_NUM));
    }

    /**
     * Fügt Optionen anhand der Übergeben DBSQL-Select-Abfrage hinzu.
     */
    public function addDBSqlOptions($qry)
    {
        $sql = rex_sql::factory();
        $this->addOptions($sql->getDBArray($qry, [], PDO::FETCH_NUM));
    }

    public function get()
    {
        $attr = '';
        foreach ($this->attributes as $name => $value) {
            $attr .= ' ' . $name . '="' . $value . '"';
        }

        $ausgabe = "\n";
        $ausgabe .= '<select' . $attr . '>' . "\n";

        foreach ($this->options as $optgroup => $options) {
            $this->currentOptgroup = $optgroup;
            if ($optgroupLabel = isset($this->optgroups[$optgroup]) ? $this->optgroups[$optgroup] : null) {
                $ausgabe .= '  <optgroup label="' . rex_escape($optgroupLabel, 'html_attr') . '">' . "\n";
            }
            if (is_array($options)) {
                $ausgabe .= $this->outGroup(0);
            }
            if ($optgroupLabel) {
                $ausgabe .= '  </optgroup>' . "\n";
            }
        }

        $ausgabe .= '</select>' . "\n";
        return $ausgabe;
    }

    public function show()
    {
        echo $this->get();
    }

    protected function outGroup($parent_id, $level = 0)
    {
        if ($level > 100) {
            // nur mal so zu sicherheit .. man weiss nie ;)
            throw new rex_exception('rex_select->outGroup overflow');
        }

        $ausgabe = '';
        $group = $this->getGroup($parent_id);
        if (!is_array($group)) {
            return '';
        }
        foreach ($group as $option) {
            $name = $option[0];
            $value = $option[1];
            $id = $option[2];
            $attributes = [];
            if (isset($option[3]) && is_array($option[3])) {
                $attributes = $option[3];
            }
            $ausgabe .= $this->outOption($name, $value, $level, $attributes);

            $subgroup = $this->getGroup($id, true);
            if ($subgroup !== false) {
                $ausgabe .= $this->outGroup($id, $level + 1);
            }
        }
        return $ausgabe;
    }

    protected function outOption($name, $value, $level = 0, array $attributes = [])
    {
        $bsps = '';
        if ($level > 0) {
            $bsps = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
        }

        if ($this->option_selected !== null && in_array($value, $this->option_selected, true)) {
            $attributes['selected'] = 'selected';
        }

        $attr = '';
        foreach ($attributes as $n => $v) {
            $attr .= ' ' . $n . '="' . $v . '"';
        }

        $name = rex_escape($name);
        $value = rex_escape($value, 'html_attr');
        return '        <option value="' . $value . '"' . $attr . '>' . $bsps . $name . '</option>' . "\n";
    }

    protected function getGroup($parent_id, $ignore_main_group = false)
    {
        if ($ignore_main_group && $parent_id == 0) {
            return false;
        }

        if (isset($this->options[$this->currentOptgroup][$parent_id])) {
            return $this->options[$this->currentOptgroup][$parent_id];
        }

        return false;
    }
}
