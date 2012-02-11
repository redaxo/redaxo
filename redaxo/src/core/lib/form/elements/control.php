<?php

class rex_form_control_element extends rex_form_element
{
  private
    $saveElement,
    $applyElement,
    $deleteElement,
    $resetElelement,
    $abortElement;

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

    $class = '';

    if($this->saveElement)
    {
      if(!$this->saveElement->hasAttribute('class'))
        $this->saveElement->setAttribute('class', 'rex-form-submit');

      $class = $this->saveElement->formatClass();

      $s .= $this->saveElement->formatElement();
    }

    if($this->applyElement)
    {
      if(!$this->applyElement->hasAttribute('class'))
        $this->applyElement->setAttribute('class', 'rex-form-submit rex-form-submit-2');

      $class = $this->applyElement->formatClass();

      $s .= $this->applyElement->formatElement();
    }

    if($this->deleteElement)
    {
      if(!$this->deleteElement->hasAttribute('class'))
        $this->deleteElement->setAttribute('class', 'rex-form-submit rex-form-submit-2');

      if(!$this->deleteElement->hasAttribute('onclick'))
        $this->deleteElement->setAttribute('onclick', 'return confirm(\''. rex_i18n::msg('form_delete') .'?\');');

      $class = $this->deleteElement->formatClass();

      $s .= $this->deleteElement->formatElement();
    }

    if($this->resetElement)
    {
      if(!$this->resetElement->hasAttribute('class'))
        $this->resetElement->setAttribute('class', 'rex-form-submit rex-form-submit-2');

      if(!$this->resetElement->hasAttribute('onclick'))
        $this->resetElement->setAttribute('onclick', 'return confirm(\''. rex_i18n::msg('form_reset') .'?\');');

      $class = $this->resetElement->formatClass();

      $s .= $this->resetElement->formatElement();
    }

    if($this->abortElement)
    {
      if(!$this->abortElement->hasAttribute('class'))
        $this->abortElement->setAttribute('class', 'rex-form-submit rex-form-submit-2');

      $class = $this->abortElement->formatClass();

      $s .= $this->abortElement->formatElement();
    }

    if ($s != '')
    {
      if ($class != '')
      {
        $class = ' '.$class;
      }
      $s = '<p class="rex-form-col-a'.$class.'">'.$s.'</p>';
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
