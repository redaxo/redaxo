<?php

/**
 * Manager for generating and validating csrf tokens.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_csrf_manager
{
    const PARAM = '_csrf_token';

    /**
     * @param string $tokenId
     *
     * @return string
     */
    public static function getToken($tokenId)
    {
        $tokens = self::getTokens();

        if (isset($tokens[$tokenId])) {
            return $tokens[$tokenId];
        }

        $token = self::generateToken();
        $tokens[$tokenId] = $token;
        rex_set_session(self::getSessionKey(), $tokens);

        return $token;
    }

    /**
     * @param string $tokenId
     *
     * @return string
     */
    public static function getHiddenField($tokenId)
    {
        return sprintf('<input type="hidden" name="%s" value="%s"/>', self::PARAM, self::getToken($tokenId));
    }

    /**
     * @param string $tokenId
     *
     * @return bool
     */
    public static function isValid($tokenId)
    {
        $tokens = self::getTokens();

        if (!isset($tokens[$tokenId])) {
            return false;
        }

        $token = rex_request(self::PARAM, 'string');

        return hash_equals($tokens[$tokenId], $token);
    }

    /**
     * @param string $tokenId
     */
    public static function removeToken($tokenId)
    {
        $tokens = self::getTokens();

        if (!isset($tokens[$tokenId])) {
            return;
        }

        unset($tokens[$tokenId]);

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
        return 'csrf_tokens_'.rex::getEnvironment();
    }

    private static function generateToken()
    {
        $bytes = random_bytes(32);

        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
