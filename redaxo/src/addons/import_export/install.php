<?php

/** @var rex_addon $this */

rex_dir::copy(
    $this->getPath('backups'),
    $this->getDataPath('backups')
);
