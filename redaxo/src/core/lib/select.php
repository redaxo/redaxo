<?php

/**
 * @package redaxo\core
 */
class rex_select
{
    private $attributes;
    private $optgroups = false;
    private $options;
    private $option_selected;

    ################ Konstruktor
    public function __construct()
    {
        $this->init();
    }

    ################ init
    public function init()
    {
        $this->attributes = [];
        $this->resetSelected();
        $this->setName('standard');
        $this->setSize('5');
        $this->setMultiple(false);
        $this->setDisabled(false);
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

    ############### multiple felder ?
    public function setMultiple($multiple = true)
    {
        if ($multiple) {
            $this->setAttribute('multiple', 'multiple');
        } else {
            $this->delAttribute('multiple');
        }
    }

    ############### disabled ?
    public function setDisabled($disabled = true)
    {
        if ($disabled) {
            $this->setAttribute('disabled', 'disabled');
        } else {
            $this->delAttribute('disabled');
        }
    }

    ################ select name
    public function setName($name)
    {
        $this->setAttribute('name', $name);
    }

    ################ select id
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

    ################ select size
    public function setSize($size)
    {
        $this->setAttribute('size', $size);
    }

    ################ selected feld - option value uebergeben
    public function setSelected($selected)
    {
        if (is_array($selected)) {
            foreach ($selected as $sectvalue) {
                $this->setSelected($sectvalue);
            }
        } else {
            $this->option_selected[] = htmlspecialchars($selected);
        }
    }

    public function resetSelected()
    {
        $this->option_selected = [];
    }

    public function useOptgroups($optgroups = true)
    {
        $this->optgroups = $optgroups;
    }

    public function addOptgroup($name, $id)
    {
        $this->options[0][] = [$name, null, $id, []];
    }

    /**
     * Fügt eine Option hinzu
     */
    public function addOption($name, $value, $id = 0, $re_id = 0, array $attributes = [])
    {
        $this->options[$re_id][] = [$name, $value, $id, $attributes];
    }

    /**
     * Fügt ein Array von Optionen hinzu, dass eine mehrdimensionale Struktur hat.
     *
     * Dim   Wert
     * 0.    Name
     * 1.    Value
     * 2.    Id
     * 3.    Re_Id
     * 4.    Selected
     * 5.    Attributes
     */
    public function addOptions($options, $useOnlyValues = false)
    {
        if (is_array($options) && count($options) > 0) {
            // Hier vorher auf is_array abfragen, da bei Strings auch die Syntax mit [] funktioniert
            // $ab = "hallo"; $ab[2] -> "l"
            $grouped = isset($options[0]) && is_array($options[0]) && isset ($options[0][2]) && isset ($options[0][3]);
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
     * Wenn $use_keys mit false, werden die Array-Keys mit den Array-Values überschrieben
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
        return $this->options ? array_sum(array_map('count', $this->options)) : 0;
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

    ############### show select
    public function get()
    {
        $attr = '';
        foreach ($this->attributes as $name => $value) {
            $attr .= ' ' . $name . '="' . $value . '"';
        }

        $ausgabe = "\n";
        $ausgabe .= '<select' . $attr . '>' . "\n";

        if (is_array($this->options)) {
            $ausgabe .= $this->outGroup(0, $this->optgroups ? -1 : 0, $this->optgroups);
        }

        $ausgabe .= '</select>' . "\n";
        return $ausgabe;
    }

    ############### show select
    public function show()
    {
        echo $this->get();
    }

    protected function outGroup($re_id, $level = 0, $optgroups = false)
    {
        if ($level > 100) {
            // nur mal so zu sicherheit .. man weiss nie ;)
            throw new rex_exception('rex_select->outGroup overflow');
        }

        $ausgabe = '';
        $group = $this->getGroup($re_id);
        if (!is_array($group)) {
            return '';
        }
        foreach ($group as $option) {
            $name = $option[0];
            $value = $option[1];
            $id = $option[2];
            if ($optgroups) {
                $ausgabe .= '    <optgroup label="' . $name . '">' . "\n";
            } else {
                $attributes = [];
                if (isset($option[3]) && is_array($option[3])) {
                    $attributes = $option[3];
                }
                $ausgabe .= $this->outOption($name, $value, $level, $attributes);
            }

            $subgroup = $this->getGroup($id, true);
            if ($subgroup !== false) {
                $ausgabe .= $this->outGroup($id, $level + 1);
            }

            if ($optgroups) {
                $ausgabe .= '    </optgroup>' . "\n";
            }
        }
        return $ausgabe;
    }

    protected function outOption($name, $value, $level = 0, array $attributes = [])
    {
        $name = htmlspecialchars($name);
        $value = htmlspecialchars($value);

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

        return '        <option value="' . $value . '"' . $attr . '>' . $bsps . $name . '</option>' . "\n";
    }

    protected function getGroup($re_id, $ignore_main_group = false)
    {
        if ($ignore_main_group && $re_id == 0) {
            return false;
        }

        foreach ($this->options as $gname => $group) {
            if ($gname == $re_id) {
                return $group;
            }
        }

        return false;
    }
}
