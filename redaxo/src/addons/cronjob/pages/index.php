<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 */

$addon = rex_addon::get('cronjob');

echo rex_view::title($addon->i18n('title'));

rex_be_controller::includeCurrentPageSubPath();
