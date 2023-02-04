<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 *
 * @template T
 */
abstract class rex_input
{
    /** @var T */
    protected $value;
    /** @var array<string, string|int> */
    protected array $attributes = [];

    public function __construct() {}

    /**
     * Setzt den Value des Input-Feldes.
     * @param T $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gibt den Wert des Input-Feldes zurueck.
     * @return T
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setzt ein HTML-Attribut des Input-Feldes.
     *
     * @param string $name
     * @param string|int $value
     *
     * @return void
     */
    public function setAttribute($name, $value)
    {
        if ('value' == $name) {
            $this->value = $value;
        } else {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * Gibt den Wert des Attributes $name zurueck falls vorhanden, sonst $default.
     *
     * @param string $name
     *
     * @return string|int|null
     */
    public function getAttribute($name, $default = null)
    {
        if ('value' == $name) {
            return $this->getValue();
        }
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Prueft ob das Input-Feld ein Attribute $name besitzt.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Fuegt dem Input-Feld die Attribute $attributes hinzu.
     *
     * @param array<string, string|int> $attributes
     *
     * @return void
     */
    public function addAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Setzt die Attribute des Input-Feldes auf $attributes.
     * Alle vorher vorhanden Attribute werden geloescht/ueberschrieben.
     *
     * @param array<string, string|int> $attributes
     *
     * @return void
     */
    public function setAttributes($attributes)
    {
        $this->attributes = [];

        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Gibt alle Attribute in Form eines Array zurueck.
     *
     * @return array<string, string|int>
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Gibt alle Attribute in String-Form zurueck.
     *
     * @return string
     */
    public function getAttributeString()
    {
        $attr = '';
        foreach ($this->attributes as $attributeName => $attributeValue) {
            $attr .= ' ' . rex_escape($attributeName) . '="' . rex_escape($attributeValue) . '"';
        }
        return $attr;
    }

    /**
     * Gibt die HTML-Representation des Input-Feldes zurueck.
     * Diese beeinhaltet alle Attribute und den Wert des Feldes.
     *
     * @return string
     */
    abstract public function getHtml();

    /**
     * Factory-Methode um rex_input_*-Elemente anhand des Types $inputType zu erstellen.
     *
     * @param string $inputType
     *
     * @return self|null
     *
     * @deprecated instantiate the concrete classes directly instead
     */
    public static function factory($inputType)
    {
        switch ($inputType) {
            case 'text':
            case 'textarea':
            case 'select':
            case 'categoryselect':
            case 'mediacategoryselect':
            case 'radio':
            case 'checkbox':
            case 'date':
            case 'time':
            case 'datetime':
            case 'mediabutton':
            case 'medialistbutton':
            case 'linkbutton':
            case 'linklistbutton':
                /** @var class-string<rex_input> $class */
                $class = 'rex_input_' . $inputType;
                return new $class();
        }
        return null;
    }
}
