<?php

/**
 * class implements storage of raw html(string) as rex_form object.
 *
 * @package redaxo\core\form
 */
class rex_form_raw_element extends rex_form_element
{
    private $html;

    public function __construct($html = '', rex_form_base $table = null)
    {
        $this->html = $html;

        parent::__construct('', $table);
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
