<?php

class rex_form_control_element extends rex_form_element
{
  var $saveElement;
  var $applyElement;
  var $deleteElement;
  var $resetElelement;
  var $abortElement;

  function rex_form_control_element(&$table, $saveElement = null, $applyElement = null, $deleteElement = null, $resetElement = null, $abortElement = null)
  {
    parent::rex_form_element('', $table);

    $this->saveElement = $saveElement;
    $this->applyElement = $applyElement;
    $this->deleteElement = $deleteElement;
    $this->resetElement = $resetElement;
    $this->abortElement = $abortElement;
  }

  function _get()
  {
    global $I18N;

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
        $this->deleteElement->setAttribute('onclick', 'return confirm(\''. $I18N->msg('form_delete') .'?\');');
			
			$class = $this->deleteElement->formatClass();

      $s .= $this->deleteElement->formatElement();
    }

    if($this->resetElement)
    {
      if(!$this->resetElement->hasAttribute('class'))
        $this->resetElement->setAttribute('class', 'rex-form-submit rex-form-submit-2');

      if(!$this->resetElement->hasAttribute('onclick'))
        $this->resetElement->setAttribute('onclick', 'return confirm(\''. $I18N->msg('form_reset') .'?\');');
			
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

  function submitted($element)
  {
    return is_object($element) && rex_post($element->getAttribute('name'), 'string') != '';
  }

  function saved()
  {
    return $this->submitted($this->saveElement);
  }

  function applied()
  {
    return $this->submitted($this->applyElement);
  }

  function deleted()
  {
    return $this->submitted($this->deleteElement);
  }

  function resetted()
  {
    return $this->submitted($this->resetElement);
  }

  function aborted()
  {
    return $this->submitted($this->abortElement);
  }
}
