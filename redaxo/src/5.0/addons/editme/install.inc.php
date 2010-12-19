<?php

/**
 * editme
 *
 * @author jan@kristinus.de
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';
if (!OOAddon::isAvailable('xform'))
{
  // Installation nicht erfolgreich
  $error = 'AddOn "XForm" ist nicht installiert und aktiviert.';

}elseif(OOAddon::getVersion('xform') < '1.7')
{
  $error = 'Das AddOn "XForm" muss mindestens in der Version 1.7 vorhanden sein.';
}

if($error == '')
{
  $REX['ADDON']['install']['editme'] = 1;
}
else
{
   $REX['ADDON']['installmsg']['editme'] = $error;
}