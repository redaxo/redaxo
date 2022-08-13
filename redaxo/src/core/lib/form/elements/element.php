<?php

/**
 * @package redaxo\core\form
 */
class rex_form_element
{
    /** @var string|int|null */
    protected $value;
    /** @var string|int|null */
    protected $defaultSaveValue = '';
    /** @var string */
    protected $label;
    /** @var string */
    protected $tag;
    /** @var rex_form_base|null */
    protected $table;
    /** @var array<string, int|string> */
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
     * @param array<string, int|string> $attributes
     * @param bool $separateEnding
     */
    public function __construct($tag, rex_form_base $form = null, array $attributes = [], $separateEnding = false)
    {
        $this->value = null;
        $this->label = '';
        $this->tag = $tag;
        $this->table = $form;
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

    /**
     * @param string|string[]|int|null $value
     * @return void
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $value = '|' . implode('|', $value) . '|';
        }
        $this->value = $value;
    }

    /**
     * @param string|int|null $value
     * @return void
     */
    public function setDefaultSaveValue($value)
    {
        $this->defaultSaveValue = $value;
    }

    /**
     * @return string|int|null
     */
    public function getSaveValue()
    {
        $value = $this->getValue();
        return '' !== $value ? $value : $this->defaultSaveValue;
    }

    /**
     * @return string|int|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setFieldName($name)
    {
        $this->fieldName = $name;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $label
     * @return void
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $notice
     * @return void
     */
    public function setNotice($notice)
    {
        $this->notice = $notice;
    }

    /**
     * @return string
     */
    public function getNotice()
    {
        return $this->notice;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $suffix
     * @return void
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $footer
     * @return void
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param string $name
     * @param int|string $value
     * @return void
     */
    public function setAttribute($name, $value)
    {
        if ('value' == $name) {
            $this->setValue($value);
        } else {
            if ('id' == $name) {
                $value = rex_string::normalize((string) $value, '-');
            } elseif ('name' == $name) {
                $value = rex_string::normalize((string) $value, '_', '[]');
            }

            $this->attributes[$name] = $value;
        }
    }

    /**
     * @template T
     * @param string $name
     * @param T $default
     * @return int|string|T
     */
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

    /**
     * @param array<string, int|string> $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];

        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * @return array<string, int|string>
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    public function isReadOnly(): bool
    {
        return str_contains((string) $this->getAttribute('class', ''), 'form-control-static');
    }

    /**
     * @return bool
     */
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
    /**
     * @return string
     */
    protected function formatClass()
    {
        return $this->getAttribute('class');
    }

    /**
     * @return string
     */
    protected function formatLabel()
    {
        $s = '';
        $label = $this->getLabel();

        if ('' != $label) {
            $s .= '<label class="control-label '.($this->isRequiredField() ? 'required' : '').'" for="' . $this->getAttribute('id').'">' . $label . '</label>';
        }

        return $s;
    }

    /**
     * @return string
     */
    public function formatElement()
    {
        $attr = '';
        $value = $this->getValue();
        $tag = rex_escape($this->getTag(), 'html_attr');

        if ($this->isRequiredField()) {
            $this->setAttribute('required', 'required');
        }

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

    /**
     * @return string
     */
    protected function formatNotice()
    {
        $notice = $this->getNotice();
        if ('' != $notice) {
            return $notice;
        }
        return '';
    }

    /**
     * @param string $content
     * @return string
     */
    protected function wrapContent($content)
    {
        return $content;
    }

    /**
     * @return string
     */
    protected function getFragment()
    {
        return 'core/form/form.php';
    }

    /**
     * @return string
     */
    protected function _get()
    {
        $formElements = [];
        $n = [];
        $n['header'] = $this->getHeader();
        $n['id'] = '';
        $n['label'] = $this->formatLabel();
        $n['before'] = $this->getPrefix();
        $n['field'] = $this->formatElement();
        $n['after'] = $this->getSuffix();
        $n['note'] = $this->formatNotice();
        $n['footer'] = $this->getFooter();
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        return $fragment->parse($this->getFragment());
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->wrapContent($this->_get());
    }

    /**
     * @return void
     */
    public function show()
    {
        echo $this->get();
    }

    private function isRequiredField(): bool
    {
        foreach ($this->getValidator()->getRules() as $rule) {
            if (rex_validation_rule::NOT_EMPTY == $rule->getType()) {
                return true;
            }
        }

        return false;
    }
}
