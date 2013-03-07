<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
 */

$rewriter = new rex_url_rewriter_fullnames();
$rewriter->prepare();

rex_extension::register('URL_REWRITE', [$rewriter, 'rewrite']);
