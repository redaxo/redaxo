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
abstract class rex_api_function
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
   * Flag indicating if the api function was executed successfully
   * @var boolean
   */
  protected $succeeded = false;

  /**
   * Statusmessage (error/info)
   * @var string
   */
  protected $message = '';


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

  /**
   * This method have to be overriden by a subclass and does all logic which the api function represents.
   *
   * In the first place this method may retrieve and validate parameters from the request.
   * Afterwards the actual logic should be executed.
   *
   * This function may also throw exceptions e.g. in case when permissions are missing or the provided parameters are invalid.
   *
   * @return string A userfriendly message for the end-user or null.
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
          throw new rexException('$apiClass is expected to define a subclass of rex_api_function!');
        }
      }
      else
      {
          throw new rexException('$apiClass "'. $apiClass .'" not found!');
      }
    }

    return null;
  }

  /**
   * checks whether an api function is bound to the current requests. If so, so the api function will be executed.
   */
  static public function handleCall()
  {
    $apiFunc = self::factory();

    if($apiFunc != null)
    {
      if($apiFunc->published === false)
      {
        if(rex::isBackend() !== true)
          throw new rexApiException('the api function '. get_class($apiFunc) .' is not published, therefore can only be called from the backend!');

        if(!rex::getUser())
          throw new rexApiException('missing backend session to call api function '. get_class($apiFunc) .'!');
      }

      try {

        $message = $apiFunc->execute();
        $apiFunc->message = $message;
        $apiFunc->succeeded = true;
      } catch (rexApiException $e)
      {
        $message = $e->getMessage();
        $apiFunc->message = $message;
        $apiFunc->succeeded = false;
      }

      // if we handle an ajax request, we direct the output to the browser and stop here
      $isAjaxRequest = rex_isXmlHttpRequest();
      if($isAjaxRequest)
      {
        echo $message;
        exit();
      }
    }
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
class rexApiException extends rexException{};