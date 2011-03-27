<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 * @version svn:$Id$
 */

abstract class rex_cronjob
{
  private
    $params = array(),
    $message = '';

  final public static function factory($class)
  {
    if (!rex_autoload::getInstance()->autoload($class))
      return $class;

    return new $class();
  }

  public function setParam($key, $value)
	{
		$this->params[$key] = $value;
	}

  public function setParams(array $params)
	{
	  $this->params = $params;
	}

	public function getParam($key, $default = null)
	{
	  if(isset($this->params[$key]))
	    return $this->params[$key];

	  return $default;
	}

	public function getParams()
	{
	  return $this->params;
	}

  public function __set($key, $value)
  {
    return $this->setParam($key, $value);
  }

  public function __get($key)
  {
    return $this->getParam($key);
  }

	public function setMessage($message)
  {
    $this->message = $message;
  }

  public function getMessage()
  {
    return $this->message;
  }

  public function hasMessage()
  {
    return !empty($this->message);
  }

  abstract public function execute();

  public function getTypeName()
  {
    // returns the name of the cronjob type
    return $this->getType();
  }

  final public function getType()
  {
    return get_class($this);
  }

  public function getEnvironments()
  {
    // returns an array of environments in which the cronjob is available
    return array('frontend', 'backend');
  }

  public function getParamFields()
  {
    // returns an array of parameters which are required for the cronjob
    return array();
  }

  final static public function isValid($cronjob)
  {
    return is_object($cronjob) && is_subclass_of($cronjob, 'rex_cronjob');
  }
}