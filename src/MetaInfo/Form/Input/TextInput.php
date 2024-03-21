<?php

namespace Redaxo\Core\MetaInfo\Form\Input;

/**
 * @internal
 *
 * @extends AbstractInput<string>
 */
class TextInput extends AbstractInput
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('class', 'form-control');
        $this->setAttribute('type', 'text');
    }

    public function getHtml()
    {
        $value = rex_escape($this->value);
        return '<input' . $this->getAttributeString() . ' value="' . $value . '" />';
    }
}
