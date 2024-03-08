<?php

use Redaxo\Core\Form\AbstractForm;

/**
 * class implements storage of raw html(string) as Form object.
 */
class rex_form_raw_element extends rex_form_element
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
