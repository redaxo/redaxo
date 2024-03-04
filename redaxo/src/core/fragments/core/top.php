<?php
use Redaxo\Core\Core;

/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<!doctype html>
<html lang="<?= rex_i18n::msg('htmllang') ?>">
<head>
    <meta charset="utf-8" />

    <title><?= $this->pageTitle ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
<?php
    $user = Core::getUser();

    $colorScheme = 'light dark'; // default: support both
    if (Core::getProperty('theme')) {
        // global theme from config.yml
        $colorScheme = rex_escape((string) Core::getProperty('theme'));
    } elseif ($user && $user->getValue('theme')) {
        // user selected theme
        $colorScheme = rex_escape($user->getValue('theme'));
    }
    echo "\n" . '    <meta name="color-scheme" content="' . $colorScheme . '">';
    echo "\n" . '    <style nonce="' . rex_response::getNonce() . '">:root { color-scheme: ' . $colorScheme . ' }</style>';

    $assetDir = rex_path::assets();

    foreach ($this->cssFiles as $media => $files) {
        foreach ($files as $file) {
            $file = (string) $file;
            $path = rex_path::frontend(rex_path::absolute($file));
            if (!Core::isDebugMode() && str_starts_with($path, $assetDir) && $mtime = @filemtime($path)) {
                $file = rex_url::backendController(['asset' => ltrim($file, '.'), 'buster' => $mtime]);
            } elseif ($mtime = @filemtime($path)) {
                $file .= '?buster=' . $mtime;
            }
            echo "\n" . '    <link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $file . '" />';
        }
    }
    echo "\n";
    echo "\n" . '    <script type="text/javascript" nonce="' . rex_response::getNonce() . '">';
    echo "\n" . '    <!--';
    echo "\n" . '    var rex = ' . $this->jsProperties . ';';
    echo "\n" . '    //-->';
    echo "\n" . '    </script>';
    foreach ($this->jsFiles as $file) {
        if (is_string($file)) {
            // BC Case
            $options = [];
        } else {
            [$file, $options] = $file;
        }

        $file = (string) $file;
        $path = rex_path::frontend(rex_path::absolute($file));
        if (array_key_exists(rex_view::JS_IMMUTABLE, $options) && $options[rex_view::JS_IMMUTABLE]) {
            if (!Core::isDebugMode() && str_starts_with($path, $assetDir) && $mtime = @filemtime($path)) {
                $file = rex_url::backendController(['asset' => ltrim($file, '.'), 'buster' => $mtime]);
            }
        } elseif ($mtime = @filemtime($path)) {
            $file .= '?buster=' . $mtime;
        }

        $attributes = [];
        if (array_key_exists(rex_view::JS_ASYNC, $options) && $options[rex_view::JS_ASYNC]) {
            $attributes[] = 'async="async"';
        }
        if (array_key_exists(rex_view::JS_DEFERED, $options) && $options[rex_view::JS_DEFERED]) {
            $attributes[] = 'defer="defer"';
        }

        echo "\n" . '    <script type="text/javascript" src="' . $file . '" ' . implode(' ', $attributes) . ' nonce="' . rex_response::getNonce() . '"></script>';
    }
?>

    <?= $this->favicon ? '<link rel="shortcut icon" href="' . $this->favicon . '" />' : '' ?>

    <link rel="apple-touch-icon" sizes="180x180" href="<?= rex_url::coreAssets('icons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= rex_url::coreAssets('icons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= rex_url::coreAssets('icons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= rex_url::coreAssets('icons/site.webmanifest') ?>">
    <link rel="mask-icon" href="<?= rex_url::coreAssets('icons/safari-pinned-tab.svg') ?>" color="<?= rex_escape((string) Core::getConfig('be_style_labelcolor', '#4d99d3')) ?>">
    <meta name="msapplication-TileColor" content="#2d89ef">

    <?= $this->pageHeader ?>

</head>
<body<?= $this->bodyAttr ?>>

<div class="rex-ajax-loader" id="rex-js-ajax-loader">
    <div class="rex-ajax-loader-element"></div>
    <div class="rex-ajax-loader-backdrop"></div>
</div>

<div id="rex-start-of-page" class="rex-page">
