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

        $vitals = rex_analytics_webvitals();
        $vitals->storeData($_SERVER['HTTP_REFERER'], $data, $article);

        exit();
    }

    rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) use ($plugin) {
        rex_analytics_extensions::injectIntoFrontend($ep, $plugin);
    });
}

if (rex::isBackend() && rex::getUser()) {
    rex_view::addCssFile($plugin->getAssetsUrl('web-vitals.css'));
}
