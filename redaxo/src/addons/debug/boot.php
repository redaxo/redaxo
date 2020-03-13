<?php

// only on http requests
if (rex::isDebugMode() && rex_server('REQUEST_URI') && 'debug' !== rex_request('rex-api-call')) {
    rex_sql::setFactoryClass('rex_sql_debug');
    rex_extension::setFactoryClass('rex_extension_debug');

    rex_logger::setFactoryClass('rex_logger_debug');
    rex_api_function::setFactoryClass('rex_api_function_debug');

    rex_response::setHeader('X-Clockwork-Id', rex_debug::getInstance()->getRequest()->id);
    rex_response::setHeader('X-Clockwork-Version', \Clockwork\Clockwork::VERSION);

    rex_response::setHeader('X-Clockwork-Path', substr(rex::getServer(), strrpos(rex::getServer(), $_SERVER['HTTP_HOST']) + strlen($_SERVER['HTTP_HOST'])).rex_url::backendController(['page' => 'structure'] + rex_api_debug::getUrlParams(), false));

    rex_extension::register('RESPONSE_SHUTDOWN', static function (rex_extension_point $ep) {
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

        $ep->table('Registered', rex_extension_debug::getRegistered());
        $ep->table('Executed', rex_extension_debug::getExecuted());

        $clockwork->resolveRequest()->storeRequest();
    }, rex_extension::LATE);

    if (rex::isBackend() && 'debug' === rex_request::get('page')) {
        $index = file_get_contents(rex_addon::get('debug')->getPath('vendor/itsgoingd/clockwork/Clockwork/Web/public/index.html'));
        $index = preg_replace('/(href|src)=("?)([^>\s]+)/', '$1=$2'.rex_addon::get('debug')->getAssetsUrl('clockwork/$3'), $index);

        rex_response::sendPage($index);
        die;
    }
}
