<?php

/**
 * Class for minibar
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_minibar
{
    public static function get()
    {
        if (!self::isActive()) {
            return null;
        }

        $collectors = [
            'rex_minibar_eggnog'
        ];
        $collectors = rex_extension::registerPoint(new rex_extension_point('MINIBAR_BARKEEPER', $collectors));

        $collectorsLoaded = [];
        if (count($collectors)) {
            foreach ($collectors as $collector) {
                $instance = new $collector();

                if ($instance instanceof rex_minibar_collector) {
                    $collectorsLoaded[] = $instance;
                }
            }
        }

        if (!count($collectorsLoaded)) {
            return null;
        }

        $fragment = new rex_fragment([
            'collectors' => $collectorsLoaded,
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
        return rex_backend_login::hasSession() && rex::getUser()->hasPerm('minibar') && rex::getUser()->getValue('minibar') == 1;
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
            rex_response::sendCookie('rex_minibar_visibility', '1', ['expires' => rex::getProperty('session_duration'), 'samesite' => 'strict']);
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
