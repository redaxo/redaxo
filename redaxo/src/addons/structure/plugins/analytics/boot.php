<?php

$plugin = rex_plugin::get('structure', 'analytics');

if (rex::isFrontend()) {
    if (rex_get('rex_analytics')) {
        // prevent session locking trough other addons
        session_abort();

        $json = file_get_contents('php://input');
        $data = json_decode($json);

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('webvitals'));
        $sql->addRecord(function (rex_sql $record) use ($data) {
            $record->setValue('uri', $_SERVER['HTTP_REFERER']);

            switch($data->name) {
                case 'CLS': {
                    $record->setValue('cls', $data->value * 1000);
                    break;
                }
                case 'FID': {
                    $record->setValue('fid', $data->value);
                    break;
                }
                case 'LCP': {
                    $record->setValue('lcp', $data->value);
                    break;
                }
                case 'TTFB': {
                    $record->setValue('ttfb', $data->value);
                    break;
                }
            }
        });
        $sql->insert();

        exit();
    }

    rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) use ($plugin) {
        $response = $ep->getSubject();
        $analyticsUrl = $url = rex_url::frontendController() . '?rex_analytics=1';

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
                webVitals.getTTFB(sendToAnalytics);
            });
        </script>';
        $response = str_ireplace('</body>', $js. '</body>', $response);
        $ep->setSubject($response);
    });
}
