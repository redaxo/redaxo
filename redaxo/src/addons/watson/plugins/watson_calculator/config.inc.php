<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author mail[at]blumbeet[dot]com Thomas Blum
 *
 */


$basedir = __DIR__;
$myaddon = 'watson_calculator';


$REX['ADDON']['rxid'][$myaddon] = '';

// Credits
$REX['ADDON']['version'][$myaddon]     = '0.0';
$REX['ADDON']['author'][$myaddon]      = 'blumbeet - web.studio';
$REX['ADDON']['supportpage'][$myaddon] = '';
$REX['ADDON']['perm'][$myaddon]        = 'admin[]';



// Check AddOns und Versionen --------------------------------------------------
if (OOPlugin::isActivated('watson', $myaddon)) {

    if ($REX['USER']) {
        require_once($basedir . '/vendor/class.SimpleCalc.php');
        require_once($basedir . '/lib/calculator.php');

        // rechte werden in der class geprüft
        $object = new watson_calculator();
        rex_register_extension('WATSON_SEARCHER', array('watson_searcher', 'registerExtension'), array('searcher' => $object));

    }
}
