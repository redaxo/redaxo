<?php

/**
 * This is a base class for all functions which a component may provide for public use.
 * Those function will be called automatically by the core.
 * Inside an api function you might check the preconditions which have to be met (permissions, etc.)
 * and forward the call to an underlying service which does the actual job.
 *
 * There can only be one rex_api_function called per request, but not every request must have an api function.
 *
 * The classname of a possible implementation must start with "rex_api".
 *
 * A api function may also be called by an ajax-request.
 * In fact there might be ajax-requests which do nothing more than triggering an api function.
 *
 * The api functions return meaningfull error messages which the caller may display to the end-user.
 *
 * @author staabm
 */
abstract class rex_api_function extends rex_factory
{
  protected function __construct()
  {
    // NOOP
  }

  /**
   * Flag, indicating if this api function may be called from the frontend. False by default
   * @var boolean
   */
  protected $published = false;

  /**
   * The result of the function call
   * @var rex_api_result
   */
  protected $result = null;

  /**
   * This method have to be overriden by a subclass and does all logic which the api function represents.
   *
   * In the first place this method may retrieve and validate parameters from the request.
   * Afterwards the actual logic should be executed.
   *
   * This function may also throw exceptions e.g. in case when permissions are missing or the provided parameters are invalid.
   *
   * @return rex_api_result The result of the api-function
   */
  public abstract function execute();

  /**
   * The api function which is bound to the current request.
   *
   * @var rex_api_function
   */
  private static $instance;

  /**
   * Returns the api function instance which is bound to the current request, or null if no api function was bound.
   *
   * @return rex_api_function
   */
  static public function factory()
  {
    if(self::$instance) return self::$instance;

    $api = rex_request('rex-api-call', 'string');

    if($api)
    {
      $apiClass = 'rex_api_'. $api;
      if(class_exists($apiClass))
      {
        $apiImpl = new $apiClass();
        if($apiImpl instanceof rex_api_function)
        {
          self::$instance = $apiImpl;
          return $apiImpl;
        }
        else
        {
          throw new rex_exception('$apiClass is expected to define a subclass of rex_api_function!');
        }
      }
      else
      {
          throw new rex_exception('$apiClass "'. $apiClass .'" not found!');
      }
    }

    return null;
  }

  /**
   * checks whether an api function is bound to the current requests. If so, so the api function will be executed.
   */
  static public function handleCall()
  {
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }

    $apiFunc = self::factory();

    if($apiFunc != null)
    {
      if($apiFunc->published !== true)
      {
        if(rex::isBackend() !== true)
          throw new rex_api_exception('the api function '. get_class($apiFunc) .' is not published, therefore can only be called from the backend!');

        if(!rex::getUser())
          throw new rex_api_exception('missing backend session to call api function '. get_class($apiFunc) .'!');
      }

      try {
        $result = $apiFunc->execute();
        $apiFunc->result = $result;
      } catch (rex_api_exception $e)
      {
        $message = $e->getMessage();
        $result = new rex_api_result(false, $message);
        $apiFunc->result = $result;
      }

      // if we handle an ajax request, we direct the output to the browser and stop here
      $isAjaxRequest = rex_isXmlHttpRequest();

      if($isAjaxRequest)
      {
        while(ob_get_level()) ob_end_clean();

        echo $result->ajaxResult();
        exit();
      }
    }
  }

  public static function getMessage()
  {
    $apiFunc = self::factory();
    $message = '';
    if($apiFunc)
    {
      $apiResult = $apiFunc->getResult();
      $message = $apiResult->getFormattedMessage();
    }
    // return a placeholder which can later be used by ajax requests to display messages
    return '<div id="rex-message-container">'. $message .'</div>';
  }

  public function getResult()
  {
    return $this->result;
  }
}

/**
 * Class representing the result of a api function call.
 *
 * @author staabm
 *
 * @see rex_api_function
 */
class rex_api_result
{
  /**
   * The inner html of the selected element will be replaced
   */
  const MODE_INNER = 'inner';

  /**
  * The selected element will be completely replaced
  */
  const MODE_REPLACE = 'replace';

  /**
  * The html will be inserted before the selected element
  */
  const MODE_BEFORE = 'before';

  /**
  * The html will be inserted after the selected element
  */
  const MODE_AFTER = 'after';

  /**
  * Flag indicating if the api function was executed successfully
  * @var boolean
  */
  private $succeeded = false;

  private $message;
  private $renderResults;

  public function __construct($succeeded, $message = null)
  {
    $this->succeeded = $succeeded;
    $this->message = $message;
  }


  public function addRenderResult($selector, $html, $selectorContext = null, $mode = null, $addClass = null, $removeClass = null)
  {
    $renderResult = array('selector' => $selector, 'html' => $html);

    if($selectorContext) $renderResult['selectorContext'] = $selectorContext;
    $renderResult['mode'] = $mode ?: self::MODE_INNER;
    if($addClass) $renderResult['addClass'] = $addClass;
    if($removeClass) $renderResult['removeClass'] = $removeClass;

    $this->renderResults[] = $renderResult;
  }

  public function ajaxResult()
  {
    $ajaxResult = array();
    $ajaxResult['renderResults'] = $this->renderResults;
    $ajaxResult['message'] = $this->getFormattedMessage();
    return json_encode($ajaxResult);
  }

  public function getFormattedMessage()
  {
    if($this->isSuccessfull())
    {
      return rex_view::info($this->message);
    }
    else
    {
      return rex_view::warning($this->message);
    }
  }

  /**
   * Returns end-user friendly statusmessage
   *
   * @return string a statusmessage
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Returns whether the api function was executed successfully
   *
   * @return boolean true on success, false on error
   */
  public function isSuccessfull()
  {
    return $this->succeeded;
  }
}

/**
 * Exception-Type to indicate exceptions in an api function.
 * The messages of this exception will be displayed to the end-user.
 *
 * @author staabm
 *
 * @see rex_api_function
 */
class rex_api_exception extends rex_exception
{
  public function __construct($message, $code = E_USER_ERROR, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}