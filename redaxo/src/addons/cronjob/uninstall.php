<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$addon = rex_addon::get('cronjob');

rex_dir::delete($addon->getDataPath());
