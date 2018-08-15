<?php

/**
 *
 * @package redaxo\core
 */
class rex_minibar
{

    private static $elements = [];

    private function __construct()
    {
    }

    public static function factory()
    {
        return new self();
    }

    public function addElement(rex_minibar_element $instance)
    {
        self::$elements[] = $instance;
    }

    public function get()
    {
        if (!self::isActive()) {
            return null;
        }

        if (!count(self::$elements)) {
            return null;
        }

        $fragment = new rex_fragment([
            'elements' => self::$elements,
        ]);

        return $fragment->parse('core/minibar/minibar.php');
    }

    /**
     * Returns if the minibar is active.
     *
     * @return bool
     */
    public static function isActive()
    {
        $user = rex_backend_login::createUser();
        if (!$user) {
            return false;
        }
        return $user->hasPerm('minibar') && $user->getValue('minibar') == 1;
    }

    /**
     * Returns if the minibar is visible.
     *
     * @return bool
     */
    public static function isVisible()
    {
        return rex_cookie('rex_minibar_visibility', 'bool', false);
    }

    /**
     * Sets the visibility.
     *
     * @param bool $value
     */
    public static function setVisibility($value)
    {
        if ($value) {
            rex_response::sendCookie('rex_minibar_visibility', '1', ['expires' => time() + rex::getProperty('session_duration'), 'samesite' => 'strict']);
        } else {
            rex_response::sendCookie('rex_minibar_visibility', '');
        }
    }
}
