<?php

$addon = rex_addon::get('install');

echo rex_view::title($addon->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

$markdown = static function (string $content): string {
    /** @var callable */
    static $markdown;

    if (!$markdown) {
        if (rex_string::versionCompare(rex::getVersion(), '5.10.0-dev', '<')) {
            // prior to 5.10 rex_markdown did not prevent xss
            // so we use Parsedown directly with enabled safe mode
            $parser = new ParsedownExtra();
            $parser->setSafeMode(true);
            $parser->setBreaksEnabled(true);

            $markdown = [$parser, 'text'];
        } else {
            $markdown = [rex_markdown::factory(), 'parse'];
        }
    }

    $fragment = new rex_fragment();
    $fragment->setVar('content', $markdown($content), false);

    return $fragment->parse('core/page/readme.php');
};

rex_be_controller::includeCurrentPageSubPath(['markdown' => $markdown]);
