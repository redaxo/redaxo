<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_cronjob_phpcode extends rex_cronjob
{ 
  /*public*/ function execute()
  {
    $code = preg_replace('/^\<\?(?:php)?/', '', $this->getParam('code'));
    $is = ini_set('display_errors', true);
    ob_start();
    $return = eval($code);
    $output = ob_get_contents();
    ob_end_clean();
    ini_set('display_errors', $is);
    if ($output)
    {
      $output = str_replace(array("\r\n\r\n", "\n\n"), "\n", trim(strip_tags($output)));
      $output = preg_replace('@in '. preg_quote(__FILE__, '@') ."\([0-9]*\) : eval\(\)'d code @", '', $output);
      $this->setMessage($output);
    }
    if ($return !== false)
      return true;
    return false;
  }
  
  /*public*/ function getTypeName()
  {
    global $I18N;
    return $I18N->msg('cronjob_type_phpcode');
  }
  
  /*public*/ function getParamFields()
	{
		global $I18N;

		return array(
  		array(
        'label' => $I18N->msg('cronjob_type_phpcode'),
        'name'  => 'code',
        'type'  => 'textarea',
        'attributes' => array('rows' => 20)
      )
    );
	}
}