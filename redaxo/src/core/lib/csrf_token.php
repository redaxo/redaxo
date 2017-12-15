<?php

/**
 * Class for generating and validating csrf tokens.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_csrf_token
{
    use rex_factory_trait;

    const PARAM = '_csrf_token';

    private $id;

    private function __construct($tokenId)
    {
        $this->id = $tokenId;
    }

    /**
     * @param string $tokenId
     *
     * @return static
     */
    public static function factory($tokenId)
    {
        $class = static::getFactoryClass();

        return new $class($tokenId);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $tokens = self::getTokens();

        if (isset($tokens[$this->id])) {
            return $tokens[$this->id];
        }

        $token = self::generateToken();
        $tokens[$this->id] = $token;
        rex_set_session(self::getSessionKey(), $tokens);

        return $token;
    }

    /**
     * @return string
     */
    public function getHiddenField()
    {
        return sprintf('<input type="hidden" name="%s" value="%s"/>', self::PARAM, $this->getValue());
    }

    /**
     * @param string $url
     * @param bool   $escape
     *
     * @return string
     */
    public function appendToUrl($url, $escape = true)
    {
        if (false === strpos($url, '?')) {
            $url .= '?';
        } else {
            $url .= $escape ? '&amp;' : '&';
        }

        return $url.self::PARAM.'='.$this->getValue();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $tokens = self::getTokens();

        if (!isset($tokens[$this->id])) {
            return false;
        }

        $token = rex_request(self::PARAM, 'string');

        return hash_equals($tokens[$this->id], $token);
    }

    public function remove()
    {
        $tokens = self::getTokens();

        if (!isset($tokens[$this->id])) {
            return;
        }

        unset($tokens[$this->id]);

        rex_set_session(self::getSessionKey(), $tokens);
    }

    public static function removeAll()
    {
        rex_login::startSession();

        rex_unset_session(self::getSessionKey());
    }

    private static function getTokens()
    {
        rex_login::startSession();

        return rex_session(self::getSessionKey(), 'array');
    }

    private static function getSessionKey()
    {
        $suffix = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']) ? '_https' : '';

        return 'csrf_tokens_'.rex::getEnvironment().$suffixs;
    }

    private static function generateToken()
    {
        $bytes = random_bytes(32);

        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
