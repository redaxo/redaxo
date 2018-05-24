<?php

if (!$this->hasConfig()) {
    $this->setConfig('labelcolor', '#43a047');
    $this->setConfig('codemirror_theme', 'eclipse');
    $this->setConfig('codemirror', 1);
    $this->setConfig('codemirror-langs', 0);
    $this->setConfig('codemirror-tools', 0);
    $this->setConfig('showlink', 1);
}
