<?php

// collect only data in debug mode with http requests outside of the debug addon
if (!rex::isDebugMode() || !rex_server('REQUEST_URI') || 'debug' === rex_get(rex_api_function::REQ_CALL_PARAM)) {
    return;
}

if (rex::isBackend() && 'debug' === rex_request::get('page')) {
    $index = file_get_contents(rex_addon::get('debug')->getAssetsPath('clockwork/index.html'));

    rex_response::sendPage($index);
    exit;
}

rex_sql::setFactoryClass(rex_sql_debug::class);
rex_extension::setFactoryClass(rex_extension_debug::class);

rex_logger::setFactoryClass(rex_logger_debug::class);
rex_api_function::setFactoryClass(rex_api_function_debug::class);

rex_response::setHeader('X-Clockwork-Id', rex_debug::getInstance()->getRequest()->id);
rex_response::setHeader('X-Clockwork-Version', \Clockwork\Clockwork::VERSION);

rex_response::setHeader('X-Clockwork-Path', rex_url::backendController(['page' => 'structure'] + rex_api_debug::getUrlParams(), false));

register_shutdown_function(static function () {
    $clockwork = rex_debug::getInstance();

    $clockwork->getTimeline()->endEvent('total');

    foreach (rex_timer::$serverTimings as $label => $timings) {
        if (!isset($timings['timings'])) {
            // compat for redaxo < 5.11
            continue;
        }

        foreach ($timings['timings'] as $i => $timing) {
            if ($timing['end'] - $timing['start'] > 0.001) {
                $clockwork->getTimeline()->addEvent($label.'_'.$i, $label, $timing['start'], $timing['end']);
            }
        }
    }

    $req = $clockwork->getRequest();

    if (rex::isBackend()) {
        $req->controller = 'page: '.rex_be_controller::getCurrentPage();
    } elseif (rex_plugin::get('structure', 'content')->isAvailable()) {
        $req->controller = 'article: '.rex_article::getCurrentId().'; clang: '.rex_clang::getCurrent()->getCode();
    }

    foreach ($req->databaseQueries as $query) {
        switch (strtolower(strtok($query['query'], ' '))) {
            case 'select':
                $req->databaseSelects++;
                break;
            case 'insert':
                $req->databaseInserts++;
                break;
            case 'update':
                $req->databaseUpdates++;
                break;
            case 'delete':
                $req->databaseDeletes++;
                break;
            default:
                $req->databaseOthers++;
                break;
        }
        if ($query['duration'] > 20) {
            ++$req->databaseSlowQueries;
        }
    }

    $ep = $clockwork->userData('ep');
    $ep->title('Extension Point');
    $ep->counters([
        'Extension Points' => count(rex_extension_debug::getExtensionPoints()),
        'Registered Extensions' => count(rex_extension_debug::getExtensions()),
    ]);

    $ep->table('Executed Extension Points', rex_extension_debug::getExtensionPoints());
    $ep->table('Registered Extensions', rex_extension_debug::getExtensions());

    $clockwork->resolveRequest()->storeRequest();
});
