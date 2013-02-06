<?php

/**
 * Generic interface for classes which provide urls.
 *
 * @author staabm
 */
interface rex_url_provider
{
  /**
   * Returns a Url which contains the given parameters.
   *
   * @param array $params A scalar array containing key value pairs for the parameter and its value.
   *
   * @return string The generated Url
   */
  public function getUrl(array $params = array());
}

/**
 * Generic interface for classes which provide a complete rex-context.
 * A rex-context consists of a set of parameters which may get passed using urls (via parameter) or forms (via hidden input fields).
 * @author staabm
 */
interface rex_context_provider extends rex_url_provider
{
  /**
   * Returns a html string containg hidden input fields for the given parameters.
   *
   * @param array $params A array containing key value pairs for the parameter and its value.
   *
   * @return string The generated html source containing the hidden input fields
   */
  public function getHiddenInputFields(array $params = array());
}

/**
 * A generic implementiation of rex_context_provider
 * @author staabm
 */
class rex_context implements rex_context_provider
{
  private
    $getParams,
    $postParams;

  /**
   * Constructs a rex_context with the given global parameters.
   *
   * @param array $getParams  An array containing scalar key value pairs for the GET params
   * @param array $postParams An array containing scalar key value pairs for the POST params
   */
  public function __construct(array $getParams = array(), array $postParams = array())
  {
    $this->getParams = $getParams;
    $this->postParams = $postParams;
  }

  /**
   * @see rex_url_provider::getUrl()
   */
  public function getUrl(array $params = array())
  {
    // combine global params with local
    $_params = array_merge($this->getParams, $params);

    return rex::isBackend() ? rex_url::backendController($_params) : rex_url::frontendController($_params);
  }

  /**
   * Returns the value associated with the given parameter $name.
   * When no value is set, $default will be returned.
   *
   * @param string $name
   * @param string $default
   */
  public function getParam($name, $default = null)
  {
    if (isset($this->getParams[$name])) {
      return $this->getParams[$name];
    }
    if (isset($this->postParams[$name])) {
      return $this->postParams[$name];
    }
    return $default;
  }

  /**
   * @see rex_context_provider::getHiddenInputFields()
   */
  public function getHiddenInputFields(array $params = array())
  {
    // combine global params with local
    $_params = array_merge($this->postParams, $params);

    return self::array2inputStr($_params);
  }

  /**
   * returns a rex_context instance containing all GET and POST parameters
   *
   * @return rex_context
   */
  static public function restore()
  {
    return new self($_GET, $_POST);
  }

  /**
   * Helper method to generate a html string with hidden input fields from an array key-value pairs
   *
   * @param array $array The array which contains the key-value pairs for convertion
   */
  static private function array2inputStr(array $array)
  {
    $inputString = '';
    foreach ($array as $name => $value) {
      if (is_array($value)) {
        foreach ($value as $valName => $valVal) {
          $inputString .= '<input type="hidden" name="' . $name . '[' . $valName . ']" value="' . htmlspecialchars($valVal) . '" />';
        }
      } else {
        $inputString .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
      }
    }

    return $inputString;
  }
}
