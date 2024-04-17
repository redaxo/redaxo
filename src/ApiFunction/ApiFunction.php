<?php

namespace Redaxo\Core\ApiFunction;

use BadMethodCallException;
use Redaxo\Core\Addon\ApiFunction\Addon;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\Base\FactoryTrait;
use Redaxo\Core\Content\ApiFunction\ArticleAdd;
use Redaxo\Core\Content\ApiFunction\ArticleCopy;
use Redaxo\Core\Content\ApiFunction\ArticleDelete;
use Redaxo\Core\Content\ApiFunction\ArticleEdit;
use Redaxo\Core\Content\ApiFunction\ArticleMove;
use Redaxo\Core\Content\ApiFunction\ArticleSliceMove;
use Redaxo\Core\Content\ApiFunction\ArticleSliceStatus;
use Redaxo\Core\Content\ApiFunction\ArticleStatus;
use Redaxo\Core\Content\ApiFunction\ArticleToCategory;
use Redaxo\Core\Content\ApiFunction\ArticleToStartArticle;
use Redaxo\Core\Content\ApiFunction\CategoryAdd;
use Redaxo\Core\Content\ApiFunction\CategoryDelete;
use Redaxo\Core\Content\ApiFunction\CategoryEdit;
use Redaxo\Core\Content\ApiFunction\CategoryMove;
use Redaxo\Core\Content\ApiFunction\CategoryStatus;
use Redaxo\Core\Content\ApiFunction\CategoryToArticle;
use Redaxo\Core\Content\ApiFunction\ContentCopy;
use Redaxo\Core\Core;
use Redaxo\Core\MetaInfo\ApiFunction\DefaultFieldsCreate;
use Redaxo\Core\Security\ApiFunction\UserHasSession;
use Redaxo\Core\Security\ApiFunction\UserImpersonate;
use Redaxo\Core\Security\ApiFunction\UserRemoveAuthMethod;
use Redaxo\Core\Security\ApiFunction\UserRemoveSession;
use Redaxo\Core\Security\CsrfToken;
use Redaxo\Core\Translation\I18n;
use rex_context;
use rex_exception;
use rex_http_exception;
use rex_response;

/**
 * This is a base class for all functions which a component may provide for public use.
 * Those function will be called automatically by the core.
 * Inside an api function you might check the preconditions which have to be met (permissions, etc.)
 * and forward the call to an underlying service which does the actual job.
 *
 * There can only be one rex_api_function called per request, but not every request must have an api function.
 *
 * The classname of a possible implementation must start with "rex_api" or must be registered explicitly via `rex_api_function::register()`.
 *
 * A api function may also be called by an ajax-request.
 * In fact there might be ajax-requests which do nothing more than triggering an api function.
 *
 * The api functions return meaningfull error messages which the caller may display to the end-user.
 *
 * Calling a api function with the backend-frontcontroller (index.php) requires a valid page parameter and the current user needs permissions to access the given page.
 *
 * @psalm-consistent-constructor
 */
abstract class ApiFunction
{
    use FactoryTrait;

    public const REQ_CALL_PARAM = 'rex-api-call';
    public const REQ_RESULT_PARAM = 'rex-api-result';

    /**
     * Flag, indicating if this api function may be called from the frontend. False by default.
     *
     * @var bool
     */
    protected $published = false;

    /**
     * The result of the function call.
     *
     * @var ApiFunctionResult|null
     */
    protected $result;

    /**
     * Explicitly registered api functions.
     *
     * @var array<string, class-string<ApiFunction>>
     */
    private static $functions = [
        'metainfo_default_fields_create' => DefaultFieldsCreate::class,
        'package' => Addon::class,
        'article2category' => ArticleToCategory::class,
        'article2startarticle' => ArticleToStartArticle::class,
        'article_add' => ArticleAdd::class,
        'article_copy' => ArticleCopy::class,
        'article_delete' => ArticleDelete::class,
        'article_edit' => ArticleEdit::class,
        'article_move' => ArticleMove::class,
        'article_status' => ArticleStatus::class,
        'category2article' => CategoryToArticle::class,
        'category_add' => CategoryAdd::class,
        'category_delete' => CategoryDelete::class,
        'category_edit' => CategoryEdit::class,
        'category_move' => CategoryMove::class,
        'category_status' => CategoryStatus::class,
        'content_copy' => ContentCopy::class,
        'content_move_slice' => ArticleSliceMove::class,
        'content_slice_status' => ArticleSliceStatus::class,
        'user_has_session' => UserHasSession::class,
        'user_impersonate' => UserImpersonate::class,
        'user_remove_auth_method' => UserRemoveAuthMethod::class,
        'user_remove_session' => UserRemoveSession::class,
    ];

    /**
     * The api function which is bound to the current request.
     *
     * @var ApiFunction|null
     */
    private static $instance;

    protected function __construct() {}

    /**
     * This method have to be overriden by a subclass and does all logic which the api function represents.
     *
     * In the first place this method may retrieve and validate parameters from the request.
     * Afterwards the actual logic should be executed.
     *
     * This function may also throw exceptions e.g. in case when permissions are missing or the provided parameters are invalid.
     *
     * @return ApiFunctionResult The result of the api-function
     */
    abstract public function execute();

    /**
     * Returns the api function instance which is bound to the current request, or null if no api function was bound.
     *
     * @throws rex_exception
     */
    public static function factory(): ?self
    {
        if (self::$instance) {
            return self::$instance;
        }

        $api = rex_request(self::REQ_CALL_PARAM, 'string');

        if ($api) {
            if (isset(self::$functions[$api])) {
                $apiClass = self::$functions[$api];
            } else {
                /** @psalm-taint-escape callable */ // It is intended that the class name suffix is coming from request param
                $apiClass = 'rex_api_' . $api;
            }

            if (class_exists($apiClass)) {
                $apiImpl = new $apiClass();
                if ($apiImpl instanceof self) {
                    self::$instance = $apiImpl;
                    return $apiImpl;
                }
                throw new rex_http_exception(new rex_exception('$apiClass is expected to define a subclass of ApiFunction, "' . $apiClass . '" given!'), rex_response::HTTP_NOT_FOUND);
            }
            throw new rex_http_exception(new rex_exception('$apiClass "' . $apiClass . '" not found!'), rex_response::HTTP_NOT_FOUND);
        }

        return null;
    }

    /**
     * @param class-string<ApiFunction> $class
     */
    public static function register(string $name, string $class): void
    {
        self::$functions[$name] = $class;
    }

    /**
     * Returns an array containing the `rex-api-call` and `_csrf_token` params.
     *
     * The method must be called on sub classes.
     *
     * @return array<string, string>
     */
    public static function getUrlParams()
    {
        $class = static::class;

        if (self::class === $class) {
            throw new BadMethodCallException(__FUNCTION__ . ' must be called on subclasses of "' . self::class . '".');
        }

        return [self::REQ_CALL_PARAM => self::getName($class), CsrfToken::PARAM => CsrfToken::factory($class)->getValue()];
    }

    /**
     * Returns the hidden fields for `rex-api-call` and `_csrf_token`.
     *
     * The method must be called on sub classes.
     *
     * @return string
     */
    public static function getHiddenFields()
    {
        $class = static::class;

        if (self::class === $class) {
            throw new BadMethodCallException(__FUNCTION__ . ' must be called on subclasses of "' . self::class . '".');
        }

        return sprintf('<input type="hidden" name="%s" value="%s"/>', self::REQ_CALL_PARAM, rex_escape(self::getName($class)))
            . CsrfToken::factory($class)->getHiddenField();
    }

    /**
     * checks whether an api function is bound to the current requests. If so, so the api function will be executed.
     *
     * @return void
     */
    public static function handleCall()
    {
        if ($factoryClass = static::getExplicitFactoryClass()) {
            $factoryClass::handleCall();
            return;
        }

        $apiFunc = self::factory();

        if (null != $apiFunc) {
            if (!$apiFunc->published) {
                if (!Core::isBackend()) {
                    throw new rex_http_exception(new ApiFunctionException('the api function ' . $apiFunc::class . ' is not published, therefore can only be called from the backend!'), rex_response::HTTP_FORBIDDEN);
                }

                if (!Core::getUser()) {
                    throw new rex_http_exception(new ApiFunctionException('missing backend session to call api function ' . $apiFunc::class . '!'), rex_response::HTTP_UNAUTHORIZED);
                }
            }

            $urlResult = rex_get(self::REQ_RESULT_PARAM, 'string');
            if ($urlResult) {
                // take over result from url and do not execute the apiFunc
                $result = ApiFunctionResult::fromJSON($urlResult);
                $apiFunc->result = $result;
            } else {
                if ($apiFunc->requiresCsrfProtection() && !CsrfToken::factory($apiFunc::class)->isValid()) {
                    $result = new ApiFunctionResult(false, I18n::msg('csrf_token_invalid'));
                    $apiFunc->result = $result;

                    return;
                }

                try {
                    $result = $apiFunc->execute();

                    if (!($result instanceof ApiFunctionResult)) {
                        throw new rex_exception('Illegal result returned from api-function ' . rex_get(self::REQ_CALL_PARAM) . '. Expected a instance of ApiFunctionResult but got "' . get_debug_type($result) . '".');
                    }

                    $apiFunc->result = $result;
                    if ($result->requiresReboot()) {
                        $context = rex_context::fromGet();
                        // add api call result to url
                        $context->setParam(self::REQ_RESULT_PARAM, $result->toJSON());
                        // and redirect to SELF for reboot
                        rex_response::sendRedirect($context->getUrl());
                    }
                } catch (ApiFunctionException $e) {
                    $message = $e->getMessage();
                    $result = new ApiFunctionResult(false, $message);
                    $apiFunc->result = $result;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public static function hasMessage()
    {
        $apiFunc = self::factory();

        if (!$apiFunc) {
            return false;
        }

        $result = $apiFunc->getResult();
        return $result && null !== $result->getMessage();
    }

    /**
     * @param bool $formatted
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
        // return a placeholder which can later be used by ajax requests to display messages
        return '<div id="rex-message-container">' . $message . '</div>';
    }

    /**
     * @return ApiFunctionResult|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Csrf validation is disabled by default for backwards compatiblity reasons. This default will change in a future version.
     * Prepare all your api functions to work with csrf token by using your-api-class::getUrlParams()/getHiddenFields(), otherwise they will stop work.
     *
     * @return bool
     */
    protected function requiresCsrfProtection()
    {
        return false;
    }

    private static function getName(string $class): string
    {
        $name = array_search($class, self::$functions, true);
        if (false !== $name) {
            return $name;
        }

        if (str_starts_with($class, 'rex_api_')) {
            return substr($class, 8);
        }

        throw new rex_exception('The api function "' . $class . '" is not registered.');
    }
}
