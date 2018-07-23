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
            'rex_minibar_information'
        ];
        $collectors = rex_extension::registerPoint(new rex_extension_point('MINIBAR_COLLECTOR', $collectors));

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
}
