<?php

final class rex_analytics_extensions {
    static public function injectIntoFrontend(\rex_extension_point $ep, rex_plugin_interface $plugin) {
        $response = $ep->getSubject();

        $analyticsUrl = $_SERVER['REQUEST_URI'];
        if (strpos($analyticsUrl, '?') === false) {
            $analyticsUrl .= '?rex_analytics=1';
        } else {
            $analyticsUrl .= '&rex_analytics=1';
        }

        $js = '<script defer src="' . $plugin->getAssetsUrl('web-vitals.min.js') .'"></script>';
        $js .= '
                <script>
                    addEventListener(\'DOMContentLoaded\', function() {
                        function sendToAnalytics(metric) {
                          const body = JSON.stringify(metric);
                          // Use `navigator.sendBeacon()` if available, falling back to `fetch()`.
                          (navigator.sendBeacon && navigator.sendBeacon(\''. $analyticsUrl .'\', body)) ||
                              fetch(\''. $analyticsUrl .'\', {body, method: \'POST\', keepalive: true});
                        }

                        webVitals.getCLS(sendToAnalytics);
                        webVitals.getFID(sendToAnalytics);
                        webVitals.getLCP(sendToAnalytics);
                    });
                </script>';
        $response = str_ireplace('</body>', $js. '</body>', $response);
        $ep->setSubject($response);
    }
}
