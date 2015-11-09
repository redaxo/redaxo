<?php

$error = '';

if($error)
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);

if (!$this->hasConfig()) {
    $this->setConfig('labelcolor', '#47a1ce');
    $this->setConfig('codemirror_theme', 'eclipse');
    $this->setConfig('codemirror', 1);
    $this->setConfig('showlink', 1);
    $this->setConfig('textarea', 1);
    $this->setConfig('liquid', 0);
    $this->setConfig('nav_flyout', 0);
}