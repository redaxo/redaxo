<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */
$file = rex_file::get(rex_path::addon('phpmailer','README.md'));
$Parsedown = new Parsedown();
$body =  '<div id="phpmailer">'.$Parsedown->text($file);

$fragment = new rex_fragment();

$fragment->setVar('body', $body, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
