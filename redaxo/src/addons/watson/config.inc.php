<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author mail[at]blumbeet[dot]com Thomas Blum
 *
 */


$basedir = __DIR__;
$myaddon = 'watson';



// Sprachdateien anhaengen
// muss wegen Developer-AddOn-Block extra sein
if ($REX['REDAXO']) {
    $I18N->appendFile($basedir . '/lang/');
}



$REX['ADDON']['rxid'][$myaddon] = '';
//$REX['ADDON']['name'][$myaddon] = $I18N->msg('b_watson_title');

// Credits
$REX['ADDON']['version'][$myaddon]     = '1.0.0';
$REX['ADDON']['author'][$myaddon]      = 'blumbeet - web.studio';
$REX['ADDON']['supportpage'][$myaddon] = '';
$REX['ADDON']['perm'][$myaddon]        = 'admin[]';
//$REX['ADDON']['navigation'][$myaddon]  = array('block' => 'developer');



// Check AddOns und Versionen --------------------------------------------------
if (OOAddon::isActivated($myaddon)) {

    require_once($basedir . '/lib/watson.php');
    require_once($basedir . '/lib/watson_legend.php');
    require_once($basedir . '/lib/watson_searcher.php');
    require_once($basedir . '/lib/watson_search_entry.php');
    require_once($basedir . '/lib/watson_search_term.php');
    require_once($basedir . '/lib/watson_search_result.php');

    require_once($basedir . '/lib/watson_extensions.php');

    if ($REX['USER']) {

        $files = array();
        $files['css']['screen'] = array('facebox.css', 'watson.css');
        $files['js']            = array('hogan.min.js', 'typeahead.js', 'facebox.js', 'watson.js');

        rex_register_extension('PAGE_HEADER'    , 'watson_extensions::page_header', $files);
        rex_register_extension('OUTPUT_FILTER'  , 'watson_extensions::agent');

        rex_register_extension('ADDONS_INCLUDED', 'watson_extensions::searcher', array(), REX_EXTENSION_LATE);

    }
}