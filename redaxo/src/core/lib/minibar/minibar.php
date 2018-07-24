<?php

/**
 * Class for debug bar
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_minibar
{
    public static function get()
    {
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
        $minibar = self::getFlags();

        return true;
        // return isset($minibar['enabled']) && $minibar['enabled'];
    }

    /**
     * Returns if the minibar is visible.
     *
     * @return bool
     */
    public static function isVisible()
    {
        $minibar = rex::getProperty('login')->getSessionVar('minibar', false);
        return isset($minibar['visible']) && $minibar['visible'];
    }

    /**
     * Sets the visibility.
     *
     * @param bool $value
     */
    public static function setVisibility($value)
    {
        $minibar = ['visible' => $value];
        rex::getProperty('login')->setSessionVar('minibar', $minibar);
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
