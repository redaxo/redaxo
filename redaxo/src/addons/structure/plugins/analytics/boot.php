<?php

$plugin = rex_plugin::get('structure', 'analytics');

rex_cronjob_manager::registerType(rex_analytics_cronjob::class);

if (rex::isFrontend()) {
    if (rex_get('rex_analytics')) {
        // prevent session locking trough other addons
        session_abort();

        $json = file_get_contents('php://input');
        $data = json_decode($json);

        $article = rex_article::getCurrent();

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('webvitals'));
        $sql->addRecord(function (rex_sql $record) use ($data, $article) {
            $record->setValue('uri', $_SERVER['HTTP_REFERER']);
            $record->setValue('article_id', $article->getId());
            $record->setValue('clang_id', $article->getClangId());

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
            }
        });
        $sql->insert();

        exit();
    }

    rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) use ($plugin) {
        rex_analytics_extensions::injectIntoFrontend($ep, $plugin);
    });
}

if (rex::isBackend() && rex::getUser()) {
    rex_view::addCssFile($plugin->getAssetsUrl('web-vitals.css'));
}
