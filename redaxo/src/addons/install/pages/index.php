<?php

$addon = rex_addon::get('install');

echo rex_view::title($addon->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

$markdown = static function (string $content): string {
    $fragment = new rex_fragment();
    $fragment->setVar('content', rex_markdown::factory()->parse($content), false);

    return $fragment->parse('core/page/readme.php');
};

rex_be_controller::includeCurrentPageSubPath(['markdown' => $markdown]);
