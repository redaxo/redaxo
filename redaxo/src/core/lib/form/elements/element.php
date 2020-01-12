<?php

/**
 * @package redaxo\core\form
 */
class rex_form_element
{
    /** @var string */
    protected $value;
    /** @var string */
    protected $defaultSaveValue = '';
    /** @var string */
    protected $label;
    /** @var string */
    protected $tag;
    /** @var rex_form_base */
    protected $table;
    /** @var array */
    protected $attributes;
    /** @var bool */
    protected $separateEnding;
    /** @var string */
    protected $fieldName;
    /** @var string */
    protected $header;
    /** @var string */
    protected $footer;
    /** @var string */
    protected $prefix;
    /** @var string */
    protected $suffix;
    /** @var string */
    protected $notice;
    /** @var rex_validator */
    protected $validator;

    /**
     * @param string $tag
     */
    public function __construct($tag, rex_form_base $table = null, array $attributes = [], $separateEnding = false)
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
        if (is_array($value)) {
            $value = '|' . implode('|', $value) . '|';
        }
        $this->value = $value;
    }

    public function setDefaultSaveValue($value)
    {
        $this->defaultSaveValue = $value;
    }

    public function getSaveValue()
    {
        $value = $this->getValue();
        return '' !== $value ? $value : $this->defaultSaveValue;
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
        if ('value' == $name) {
            $this->setValue($value);
        } else {
            if ('id' == $name) {
                $value = rex_string::normalize($value, '-');
            } elseif ('name' == $name) {
                $value = rex_string::normalize($value, '_', '[]');
            }

            $this->attributes[$name] = $value;
        }
    }

    public function getAttribute($name, $default = null)
    {
        if ('value' == $name) {
            return $this->getValue();
        }
        if ($this->hasAttribute($name)) {
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

    /**
     * @return rex_validator
     */
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

        if ('' != $label) {
            $s .= '<label class="control-label" for="' . $this->getAttribute('id') . '">' . $label . '</label>';
        }

        return $s;
    }

    public function formatElement()
    {
        $attr = '';
        $value = $this->getValue();
        $tag = rex_escape($this->getTag(), 'html_attr');

        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            $attr .= ' ' . rex_escape($attributeName, 'html_attr') . '="' . rex_escape($attributeValue) . '"';
        }

        if ($this->hasSeparateEnding()) {
            if ('button' == $tag) {
                $attr .= ' value="1"';
            }
            return '<' . $tag . $attr . '>' . rex_escape($value) . '</' . $tag . '>';
        }
        $attr .= ' value="' . rex_escape($value) . '"';
        return '<' . $tag . $attr . ' />';
    }

    protected function formatNotice()
    {
        $notice = $this->getNotice();
        if ('' != $notice) {
            return $notice;
        }
        return '';
    }

    protected function wrapContent($content)
    {
        return $content;
    }

    protected function getFragment()
    {
        return 'core/form/form.php';
    }

    protected function _get()
    {
        $class = $this->formatClass();
        $class = '' == $class ? '' : ' ' . $class;

        $formElements = [];
        $n = [];
        $n['header'] = $this->getHeader();
        $n['id'] = '';
        //$n['class']     = $class;
        $n['label'] = $this->formatLabel();
        $n['before'] = $this->getPrefix();
        $n['field'] = $this->formatElement();
        $n['after'] = $this->getSuffix();
        $n['note'] = $this->formatNotice();
        $n['footer'] = $this->getFooter();
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        return  $fragment->parse($this->getFragment());
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
