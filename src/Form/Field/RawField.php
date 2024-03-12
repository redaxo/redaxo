<?php

namespace Redaxo\Core\Form\Field;

use Redaxo\Core\Form\AbstractForm;

/**
 * class implements storage of raw html(string) as Form object.
 */
class RawField extends BaseField
{
    /** @param string $html */
    public function __construct(
        private $html = '',
        ?AbstractForm $form = null,
    ) {
        parent::__construct('', $form);
    }

    public function formatElement()
    {
        return $this->html;
    }

    public function get()
    {
        return $this->html;
    }
}
