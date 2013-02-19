<?php

/**
 * class implements storage of raw html(string) as rex_form object
 */
class rex_form_raw_element extends rex_form_element
{
    private $html;

    public function __construct($html = '')
    {
        $this->html = $html;
    }

    public function formatElement()
    {
        return $this->html;
    }

    public function get()
    {
        return $this->html;
    }

    public function wrapContent()
    {
        return $this->html;
    }
}
