<?php

use Redaxo\Core\Form\AbstractForm;
use Redaxo\Core\Form\Field\BaseField;

/**
 * class implements storage of raw html(string) as Form object.
 */
class rex_form_raw_element extends BaseField
{
    /** @var string */
    private $html;

    /** @param string $html */
    public function __construct($html = '', ?AbstractForm $form = null)
    {
        $this->html = $html;

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
