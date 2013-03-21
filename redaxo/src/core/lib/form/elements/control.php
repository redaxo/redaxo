<?php

/**
 * @package redaxo\core
 */
class rex_form_control_element extends rex_form_element
{
    private $saveElement;
    private $applyElement;
    private $deleteElement;
    private $resetElement;
    private $abortElement;

    public function __construct(rex_form $table, rex_form_element $saveElement = null, rex_form_element $applyElement = null, rex_form_element $deleteElement = null, rex_form_element $resetElement = null, rex_form_element $abortElement = null)
    {
        parent::__construct('', $table);

        $this->saveElement = $saveElement;
        $this->applyElement = $applyElement;
        $this->deleteElement = $deleteElement;
        $this->resetElement = $resetElement;
        $this->abortElement = $abortElement;
    }

    protected function _get()
    {
        $s = '';
        $elements = [];

        if ($this->saveElement) {
            if (!$this->saveElement->hasAttribute('class')) {
                $this->saveElement->setAttribute('class', 'rex-button');
            }

            $e = [];
            $e['class'] = $this->saveElement->formatClass();
            $e['field'] = $this->saveElement->formatElement();
            $elements[] = $e;
        }

        if ($this->applyElement) {
            if (!$this->applyElement->hasAttribute('class')) {
                $this->applyElement->setAttribute('class', 'rex-button');
            }

            $e = [];
            $e['class'] = $this->applyElement->formatClass();
            $e['field'] = $this->applyElement->formatElement();
            $elements[] = $e;
        }

        if ($this->deleteElement) {
            if (!$this->deleteElement->hasAttribute('class')) {
                $this->deleteElement->setAttribute('class', 'rex-button');
            }

            if (!$this->deleteElement->hasAttribute('onclick')) {
                $this->deleteElement->setAttribute('data-confirm', rex_i18n::msg('form_delete') . '?');
            }

            $e = [];
            $e['class'] = $this->deleteElement->formatClass();
            $e['field'] = $this->deleteElement->formatElement();
            $elements[] = $e;
        }

        if ($this->resetElement) {
            if (!$this->resetElement->hasAttribute('class')) {
                $this->resetElement->setAttribute('class', 'rex-button');
            }

            if (!$this->resetElement->hasAttribute('onclick')) {
                $this->resetElement->setAttribute('data-confirm', rex_i18n::msg('form_reset') . '?');
            }

            $e = [];
            $e['class'] = $this->resetElement->formatClass();
            $e['field'] = $this->resetElement->formatElement();
            $elements[] = $e;
        }

        if ($this->abortElement) {
            if (!$this->abortElement->hasAttribute('class')) {
                $this->abortElement->setAttribute('class', 'rex-button');
            }

            $e = [];
            $e['class'] = $this->abortElement->formatClass();
            $e['field'] = $this->abortElement->formatElement();
            $elements[] = $e;
        }

        if (count($elements) > 0) {
            $fragment = new rex_fragment();
            $fragment->setVar('elements', $elements, false);
            $s = $fragment->parse('core/rex_form/submit.php');
        }

        return $s;
    }

    public function submitted($element)
    {
        return is_object($element) && rex_post($element->getAttribute('name'), 'string') != '';
    }

    public function saved()
    {
        return $this->submitted($this->saveElement);
    }

    public function applied()
    {
        return $this->submitted($this->applyElement);
    }

    public function deleted()
    {
        return $this->submitted($this->deleteElement);
    }

    public function resetted()
    {
        return $this->submitted($this->resetElement);
    }

    public function aborted()
    {
        return $this->submitted($this->abortElement);
    }
}
