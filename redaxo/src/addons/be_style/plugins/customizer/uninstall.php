<?php

$error = '';

if($error)
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', false);