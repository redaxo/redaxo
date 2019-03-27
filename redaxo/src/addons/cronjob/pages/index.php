<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

echo rex_view::title($this->i18n('title'));

rex_be_controller::includeCurrentPageSubPath();
