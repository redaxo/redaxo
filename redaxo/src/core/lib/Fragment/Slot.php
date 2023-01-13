<?php

namespace Redaxo\Core\Fragment;

use DOMDocument;
use rex_functional_exception;

class Slot
{
    public function __construct(
        public string $value,
    ) {
    }

    public function get(): string
    {
        return $this->value;
    }

    public function prepare(string $value): static
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML($this->value, LIBXML_COMPACT | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOENT | LIBXML_NOWARNING);
        $element = $document->firstElementChild;
        if (!$element) {
            throw new rex_functional_exception('Element not found. The element requires the attribute `slot="'.$value.'"`');
        }
        if (!$element->hasAttribute('slot')) {
            $element->setAttribute('slot', $value);
        }
        $this->value = $document->saveHTML();
        return $this;
    }
}
