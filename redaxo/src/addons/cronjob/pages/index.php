<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$myaddon = rex_addon::get('cronjob');

echo rex_view::title($myaddon->i18n('title'));

rex_be_controller::includeCurrentPageSubPath();
