<?php

namespace Redaxo\Core\MetaInfo\Form\Input;

/**
 * @internal
 *
 * @extends AbstractInput<string>
 */
class TextareaInput extends AbstractInput
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('class', 'form-control');
        $this->setAttribute('cols', '50');
        $this->setAttribute('rows', '6');
    }

    public function getHtml()
    {
        $value = rex_escape($this->value);
        return '<textarea' . $this->getAttributeString() . '>' . $value . '</textarea>';
    }
}
