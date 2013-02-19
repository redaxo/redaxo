<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

// Parameter
$Basedir = __DIR__;

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');

echo rex_view::title('Metainformationen erweitern');

echo '<div class="rex-addon-output-v2">';

// Include Current Page
switch ($subpage) {
    case 'media' :
    {
        $prefix = 'med_';
        break;
    }
    case 'categories' :
    {
        $prefix = 'cat_';
        break;
    }
    default:
    {
        $prefix = 'art_';
    }
}

$metaTable = rex_metainfo_meta_table($prefix);

require $Basedir . '/field.php';

echo '</div>';
