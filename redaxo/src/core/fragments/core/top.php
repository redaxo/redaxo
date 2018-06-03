<!doctype html>
<html lang="<?php echo  rex_i18n::msg('htmllang'); ?>">
<head>
    <meta charset="utf-8" />

    <title><?php echo $this->pageTitle ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
<?php

    foreach ($this->cssFiles as $media => $files) {
        foreach ($files as $file) {
            $path = rex_path::base(rex_path::absolute($file));
            if ($mtime = @filemtime($path)) {
                $file .= '?buster='. $mtime;
            }
            echo "\n" . '    <link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $file .'" />';
        }
    }
    echo "\n";
    echo "\n" . '    <script type="text/javascript">';
    echo "\n" . '    <!--';
    echo "\n" . '    var rex = '.$this->jsProperties.';';
    echo "\n" . '    //-->';
    echo "\n" . '    </script>';
    foreach ($this->jsFiles as $file) {
        $path = rex_path::base(rex_path::absolute($file));
        if ($mtime = @filemtime($path)) {
            $file .= '?buster='. $mtime;
        }
        echo "\n" . '    <script type="text/javascript" src="' . $file .'"></script>';
    }
?>

    <?php echo $this->favicon ? '<link rel="shortcut icon" href="' . $this->favicon . '" />' : '' ?>

    <?php echo $this->pageHeader ?>

</head>
<body<?php echo $this->bodyAttr; ?>>

<div class="rex-ajax-loader" id="rex-js-ajax-loader">
    <div class="rex-ajax-loader-elements">
        <div class="rex-ajax-loader-element1 rex-ajax-loader-element"></div>
        <div class="rex-ajax-loader-element2 rex-ajax-loader-element"></div>
        <div class="rex-ajax-loader-element3 rex-ajax-loader-element"></div>
        <div class="rex-ajax-loader-element4 rex-ajax-loader-element"></div>
        <div class="rex-ajax-loader-element5 rex-ajax-loader-element"></div>
    </div>
</div>
<div id="rex-start-of-page" class="rex-page">
