<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$myaddon = rex_addon::get('phpmailer');

echo rex_view::title($myaddon->i18n('title'));

rex_be_controller::includeCurrentPageSubPath();
