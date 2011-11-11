<?php

#var_dump($GLOBALS);exit;

rex_title($this->getName());

echo $OLD_I18N->msg('test23');

echo count($REX['PERM']);

foreach($REX['PERM'] as $perm)
  echo $perm;