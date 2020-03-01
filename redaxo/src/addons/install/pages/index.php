<?php

$addon = rex_addon::get('install');

echo rex_view::title($addon->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

/**
 * @package redaxo\install
 *
 * @internal
 */
function rex_install_markdown(string $content): string
{
    static $markdown;

    if (!$markdown) {
        $parser = rex_markdown::factory();
        $stripTags = rex_string::versionCompare(rex::getVersion(), '5.10.0-dev', '<');

        $markdown = static function (string $content) use ($parser, $stripTags): string {
            if ($stripTags) {
                $content = strip_tags($content);
            }

            return $parser->parse($content);
        };
    }

    $fragment = new rex_fragment();
    $fragment->setVar('content', $markdown($content), false);

    return $fragment->parse('core/page/readme.php');
}

rex_be_controller::includeCurrentPageSubPath();
