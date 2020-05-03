<?php

// collect only data in debug mode with http requests outside of the debug addon
if (!rex::isDebugMode() || !rex_server('REQUEST_URI') || 'debug' === rex_get(rex_api_function::REQ_CALL_PARAM)) {
    return;
}

if (rex::isBackend() && 'debug' === rex_request::get('page')) {
    $index = file_get_contents(rex_addon::get('debug')->getAssetsPath('clockwork/index.html'));

    $editor = rex_editor::factory();
    $curEditor = $editor->getName();
    $editorBasepath = $editor->getBasepath();

    $siteKey = rex_debug_clockwork::getFullClockworkApiUrl();
    $localPath = null;
    $realPath = null;

    if ($editorBasepath) {
        $localPath = rex_escape($editorBasepath, 'js');
        $realPath = rex_escape(rex_path::base(), 'js');
    }

    $injectedScript = <<<EOF
    <script>
        let store;
        try {
            store = JSON.parse(localStorage.getItem('clockwork'));
        } catch (e) {
            store = {};
        }

        if (!store) store = {};
        if (!store.settings) store.settings = {};
        if (!store.settings.global) store.settings.global = {};

        store.settings.global.editor = '$curEditor';
        store.settings.global.seenReleaseNotesVersion = "4.1";

        if (!store.settings.site) store.settings.site = {};

        store.settings.site['$siteKey'] = {localPathMap: {local: "$localPath", real: "$realPath"}};

        localStorage.setItem('clockwork', JSON.stringify(store))
    </script>
EOF;

    $index = str_replace('<body>', '<body>'.$injectedScript, $index);
    rex_response::sendPage($index);
    exit;
}

rex_sql::setFactoryClass(rex_sql_debug::class);
rex_extension::setFactoryClass(rex_extension_debug::class);

rex_logger::setFactoryClass(rex_logger_debug::class);
rex_api_function::setFactoryClass(rex_api_function_debug::class);

rex_response::setHeader('X-Clockwork-Id', rex_debug_clockwork::getInstance()->getRequest()->id);
rex_response::setHeader('X-Clockwork-Version', \Clockwork\Clockwork::VERSION);

rex_response::setHeader('X-Clockwork-Path', rex_debug_clockwork::getClockworkApiUrl());

register_shutdown_function(static function () {
    $clockwork = rex_debug_clockwork::getInstance();

    $clockwork->getTimeline()->endEvent('total');

    foreach (rex_timer::$serverTimings as $label => $timings) {
        foreach ($timings['timings'] as $i => $timing) {
            if ($timing['end'] - $timing['start'] >= 0.001) {
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
        switch (rex_sql::getQueryType($query['query'])) {
            case 'SELECT':
                $req->databaseSelects++;
                break;
            case 'INSERT':
                $req->databaseInserts++;
                break;
            case 'UPDATE':
                $req->databaseUpdates++;
                break;
            case 'DELETE':
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
