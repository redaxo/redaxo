<?php

/**
 * Class for debug bar
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_debug_bar
{
    public static function get()
    {
        $collectors = [
            'rex_debug_bar_information'
        ];
        $collectors = rex_extension::registerPoint(new rex_extension_point('DEBUG_BAR_COLLECTOR', $collectors));

        $collectorsLoaded = [];
        if (count($collectors)) {
            foreach ($collectors as $collector) {
                $instance = new $collector();

                if ($instance instanceof rex_debug_bar_collector) {
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

        return $fragment->parse('core/debug_bar/debug_bar.php');
    }
}
