<?php

/**
 * @package redaxo\core
 */
class rex_form_element
{
    protected $value;
    protected $defaultSaveValue = '';
    protected $label;
    protected $tag;
    protected $table;
    protected $attributes;
    protected $separateEnding;
    protected $fieldName;
    protected $header;
    protected $footer;
    protected $prefix;
    protected $suffix;
    protected $notice;
    /** @var rex_validator */
    protected $validator;

    public function __construct($tag, rex_form $table = null, array $attributes = [], $separateEnding = false)
    {
        $this->value = null;
        $this->label = '';
        $this->tag = $tag;
        $this->table = $table;
        $this->setAttributes($attributes);
        $this->separateEnding = $separateEnding;
        $this->setHeader('');
        $this->setFooter('');
        $this->setPrefix('');
        $this->setSuffix('');
        $this->fieldName = '';
        $this->validator = rex_validator::factory();
    }

    // --------- Attribute setter/getters

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setDefaultSaveValue($value)
    {
        $this->defaultSaveValue = $value;
    }

    public function getSaveValue()
    {
        return $this->getValue() ?: $this->defaultSaveValue;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setFieldName($name)
    {
        $this->fieldName = $name;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setNotice($notice)
    {
        $this->notice = $notice;
    }

    public function getNotice()
    {
        return $this->notice;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function getFooter()
    {
        return $this->footer;
    }

    public function setAttribute($name, $value)
    {
        if ($name == 'value') {
            $this->setValue($value);
        } else {
            if ($name == 'id') {
                $value = rex_string::normalize($value, '-');
            } elseif ($name == 'name') {
                $value = rex_string::normalize($value, '_', '[]');
            }

            $this->attributes[$name] = $value;
        }
    }

    public function getAttribute($name, $default = null)
    {
        if ($name == 'value') {
            return $this->getValue();
        } elseif ($this->hasAttribute($name)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = [];

        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    public function hasSeparateEnding()
    {
        return $this->separateEnding;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    // --------- Element Methods

    protected function formatClass()
    {
        return $this->getAttribute('class');
    }

    protected function formatLabel()
    {
        $s = '';
        $label = $this->getLabel();

        if ($label != '') {
            $s .= '                    <label for="' . $this->getAttribute('id') . '">' . $label . '</label>' . "\n";
        }

        return $s;
    }

    public function formatElement()
    {
        $attr = '';
        $value = htmlspecialchars($this->getValue());
        $tag = htmlspecialchars($this->getTag());

        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            $attr .= ' ' . htmlspecialchars($attributeName) . '="' . htmlspecialchars($attributeValue) . '"';
        }

        if ($this->hasSeparateEnding()) {
            return '                    <' . $tag . $attr . '>' . $value . '</' . $tag . '>' . "\n";
        } else {
            $attr .= ' value="' . $value . '"';
            return '                    <' . $tag . $attr . ' />' . "\n";
        }
    }

    protected function formatNotice()
    {
        $notice = $this->getNotice();
        if ($notice != '') {
            return $notice;
        }
        return '';
    }

    protected function wrapContent($content)
    {
        return $content;
    }

    protected function _get()
    {
        $class = $this->formatClass();
        $class = $class == '' ? '' : ' ' . $class;

        $formElements = [];
        $n = [];
        $n['header']    = $this->getHeader();
        $n['id']        = '';
        $n['class']     = $class;
        $n['label']     = $this->formatLabel();
        $n['before']    = $this->getPrefix();
        $n['field']     = $this->formatElement();
        $n['after']     = $this->getSuffix();
        $n['note']      = $this->formatNotice();
        $n['footer']    = $this->getFooter();
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        return  $fragment->parse('core/rex_form/form.php');
    }

    public function get()
    {
        $s = $this->wrapContent($this->_get());
        return $s;
    }

    public function show()
    {
        echo $this->get();
    }
}
