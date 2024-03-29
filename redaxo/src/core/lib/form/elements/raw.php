<?php

/**
 * class implements storage of raw html(string) as rex_form object.
 *
 * @package redaxo\core\form
 */
class rex_form_raw_element extends rex_form_element
{
    /** @param string $html */
    public function __construct(
        private $html = '',
        ?rex_form_base $form = null,
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
