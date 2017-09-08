<?php

if ($this->getConfig('backup') == '') {
    $this->setConfig('backup', 1);
}
