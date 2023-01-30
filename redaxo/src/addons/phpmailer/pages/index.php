<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$addon = rex_addon::get('phpmailer');

echo rex_view::title($addon->i18n('title'));

rex_be_controller::includeCurrentPageSubPath();
