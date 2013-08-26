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
 * @package redaxo\core
 */
abstract class rex_api_function
{
    use rex_factory_trait;

    const REQ_CALL_PARAM = 'rex-api-call';

    const REQ_RESULT_PARAM = 'rex-api-result';

    /**
     * Flag, indicating if this api function may be called from the frontend.
     * False by default
     *
     * @var boolean
     */
    protected $published = false;

    /**
     * The result of the function call
     *
     * @var rex_api_result
     */
    protected $result = null;

    /**
     * This method have to be overriden by a subclass and does all logic which
     * the api function represents.
     *
     * In the first place this method may retrieve and validate parameters from
     * the request.
     * Afterwards the actual logic should be executed.
     *
     * This function may also throw exceptions e.g. in case when permissions are
     * missing or the provided parameters are invalid.
     *
     * @return rex_api_result The result of the api-function
     */
    abstract public function execute();

    /**
     * The api function which is bound to the current request.
     *
     * @var rex_api_function
     */
    private static $instance;

    /**
     * Returns the api function instance which is bound to the current request,
     * or null if no api function was bound.
     *
     * @throws rex_exception
     * @return self
     */
    public static function factory()
    {
        if (self::$instance) {
            return self::$instance;
        }

        $api = rex_request(self::REQ_CALL_PARAM, 'string');

        if ($api) {
            $apiClass = 'rex_api_' . $api;
            if (class_exists($apiClass)) {
                $apiImpl = new $apiClass();
                if ($apiImpl instanceof self) {
                    self::$instance = $apiImpl;
                    return $apiImpl;
                } else {
                    throw new rex_exception('$apiClass is expected to define a subclass of rex_api_function!');
                }
            } else {
                throw new rex_exception('$apiClass "' . $apiClass . '" not found!');
            }
        }

        return null;
    }

    /**
     * checks whether an api function is bound to the current requests.
     * If so, so the api function will be executed.
     */
    public static function handleCall()
    {
        if (static::hasFactoryClass()) {
            return static::callFactoryClass(__FUNCTION__, func_get_args());
        }

        $apiFunc = self::factory();

        if ($apiFunc != null) {
            if ($apiFunc->published !== true) {
                if (rex::isBackend() !== true) {
                    throw new rex_http_exception(new rex_api_exception('the api function ' . get_class($apiFunc) . ' is not published, therefore can only be called from the backend!'), rex_response::HTTP_FORBIDDEN);
                }

                if (! rex::getUser()) {
                    throw new rex_http_exception(new rex_api_exception('missing backend session to call api function ' . get_class($apiFunc) . '!'), rex_response::HTTP_UNAUTHORIZED);
                }
            }

            $urlResult = rex_get(self::REQ_RESULT_PARAM, 'string');
            if ($urlResult) {
                // take over result from url and do not execute the apiFunc
                $result = rex_api_result::fromJson($urlResult);
                $apiFunc->result = $result;
            } else {
                try {

                    $result = $apiFunc->execute();

                    if (! ($result instanceof rex_api_result_abstract)) {
                        throw new rex_exception('Illegal result returned from api-function ' . rex_get(self::REQ_CALL_PARAM));
                    }

                    $apiFunc->result = $result;
                    if ($result->requiresReboot()) {
                        $context = rex_context::restore();
                        // add api call result to url
                        $context->setParam(self::REQ_RESULT_PARAM, $result->toJson());
                        // and redirect to SELF for reboot
                        rex_response::sendRedirect($context->getUrl([], false));
                    }

                    // requests for json will get api-result immediately
                    if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                        rex_response::sendContent($result->toJson(), 'application/json');
                        exit();
                    }
                } catch (rex_api_exception $e) {
                    $message = $e->getMessage();
                    $result = new rex_api_result(false, $message);
                    $apiFunc->result = $result;

                    // requests for json will get api-result immediately
                    if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                        rex_response::setStatus(rex_response::HTTP_INTERNAL_ERROR);
                        rex_response::sendContent($result->toJson(), 'application/json');
                        exit();
                    }
                }
            }
        }
    }

    /**
     *
     * @return boolean
     */
    public static function hasMessage()
    {
        $apiFunc = self::factory();
        return (boolean) $apiFunc->getResult();
    }

    /**
     *
     * @param boolean $formatted
     *
     * @return string
     */
    public static function getMessage($formatted = true)
    {
        $apiFunc = self::factory();
        $message = '';
        if ($apiFunc) {
            $apiResult = $apiFunc->getResult();
            if ($apiResult) {
                if ($formatted) {
                    $message = $apiResult->getFormattedMessage();
                } else {
                    $message = $apiResult->getMessage();
                }
            }
        }
        // return a placeholder which can later be used by ajax requests to
        // display messages
        return '<div id="rex-message-container">' . $message . '</div>';
    }

    protected function __construct()
    {
        // NOOP
    }

    /**
     *
     * @return rex_api_result
     */
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
 * @package redaxo\core
 */
class rex_api_result extends rex_api_result_abstract
{

    /**
     *
     * @param boolean $succeeded
     * @param string  $message
     */
    public function __construct($succeeded, $message = null)
    {
        parent::__construct($succeeded, $message);
    }

    /**
     * Returns a json representation of the result object
     *
     * @return string
     */
    public function toJson()
    {
        $data = [];
        foreach ($this as $key => $value) {
            $data[$key] = $value;
        }
        return json_encode($data);
    }

    /**
     * Creates a rex_api_result object from the given JSON string
     *
     * @param string $json
     * @return self
     */
    public static function fromJson($json)
    {
        $result = new self(true);
        $json = json_decode($json, true);
        foreach ($json as $key => $value) {
            $result->$key = $value;
        }
        return $result;
    }
}

/**
 * Abstract baseclass of a api result.
 *
 * @author staabm
 *
 * @see rex_api_function
 * @package redaxo\core
 */
abstract class rex_api_result_abstract
{

    /**
     * Flag indicating if the api function was executed successfully
     *
     * @var boolean
     */
    private $succeeded = false;

    /**
     * Optional message which will be visible to the end-user
     *
     * @var string
     */
    private $message;

    /**
     * Flag indicating whether the result of this api call needs to be rendered
     * in a new sub-request.
     * This is required in rare situations, when some low-level data was changed
     * by the api-function.
     *
     * @var boolean
     */
    private $requiresReboot;

    /**
     *
     * @param boolean $succeeded
     * @param string  $message
     */
    public function __construct($succeeded, $message = null)
    {
        $this->succeeded = $succeeded;
        $this->message = $message;
        $this->requiresReboot = false;
    }

    /**
     *
     * @param boolean $requiresReboot
     */
    public function setRequiresReboot($requiresReboot)
    {
        $this->requiresReboot = $requiresReboot;
    }

    /**
     * Returns whether the result of api function requires a full reboot to take
     * effect.
     *
     * @return boolean
     */
    public function requiresReboot()
    {
        return $this->requiresReboot;
    }

    /**
     * Returns the message formatted as error or success, depending on the
     * success-property
     *
     * @return string
     */
    public function getFormattedMessage()
    {
        if ($this->isSuccessfull()) {
            return rex_view::success($this->message);
        } else {
            return rex_view::error($this->message);
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

    /**
     * Returns a json representation of the api result
     *
     * @return string
     */
    abstract public function toJson();
}

/**
 * Exception-Type to indicate exceptions in an api function.
 * The messages of this exception will be displayed to the end-user.
 *
 * @author staabm
 *
 * @see rex_api_function
 * @package redaxo\core
 */
class rex_api_exception extends rex_exception
{

    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
