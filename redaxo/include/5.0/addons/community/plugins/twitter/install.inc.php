<?php

$pv = (int) phpversion();

if($pv>4)
{
  $REX['ADDON']['install']['twitter'] = 1;
}else
{
  $REX['ADDON']['install']['twitter'] = 0;
  $REX['ADDON']['installmsg']['twitter'] = 'Es muss mindestens PHP in der Version 5 vorhanden sein';

}

?>