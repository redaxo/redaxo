<?php

/**
 *
 * @package redaxo\core
 */
class rex_minibar
{
    private function __construct()
    {
    }

    public static function factory()
    {
        return new self();
    }

    public function get()
    {
        if (!self::isActive()) {
            return null;
        }

        $drinks = [
            'rex_minibar_eggnog'
        ];
        $drinks = rex_extension::registerPoint(new rex_extension_point('MINIBAR_BARKEEPER', $drinks));

        $drinksLoaded = [];
        if (count($drinks)) {
            foreach ($drinks as $drink) {
                $instance = new $drink();

                if ($instance instanceof rex_minibar_drink) {
                    $drinksLoaded[] = $instance;
                }
            }
        }

        if (!count($drinksLoaded)) {
            return null;
        }

        $fragment = new rex_fragment([
            'drinks' => $drinksLoaded,
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
        return rex_backend_login::hasSession() && $user->hasPerm('minibar') && $user->getValue('minibar') == 1;
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

    /**
     * Returns the minibar flags.
     *
     * @return array
     */
    public static function getFlags()
    {
        return rex::getProperty('minibar', []);
    }
}
