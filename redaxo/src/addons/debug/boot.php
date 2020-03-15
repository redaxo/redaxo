<?php

// collect only data in debug mode with http requests outside of the debug addon
if (!rex::isDebugMode() || !rex_server('REQUEST_URI') || 'debug' === rex_get(rex_api_function::REQ_CALL_PARAM)) {
    return;
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

    $req = $clockwork->getRequest();
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
        'Registered' => count(rex_extension_debug::getRegistered()),
        'Executed' => count(rex_extension_debug::getExecuted()),
    ]);

    $ep->table('Executed', rex_extension_debug::getExecuted());
    $ep->table('Registered', rex_extension_debug::getRegistered());

    $clockwork->resolveRequest()->storeRequest();
});

if (rex::isBackend() && 'debug' === rex_request::get('page')) {
    $index = file_get_contents(rex_addon::get('debug')->getAssetsPath('clockwork/index.html'));
    $index = preg_replace('/(href|src)=("?)([^>\s]+)/', '$1=$2'.rex_addon::get('debug')->getAssetsUrl('clockwork/$3'), $index);

    rex_response::sendPage($index);
    exit;
}
