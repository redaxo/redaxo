<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$myaddon = rex_addon::get('cronjob');

rex_dir::delete($myaddon->getDataPath());
