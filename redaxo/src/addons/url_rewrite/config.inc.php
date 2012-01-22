<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
 * @version svn:$Id$
 */

$rewriter = new rex_urlRewriter_fullnames();
$rewriter->prepare();

rex_extension::register('URL_REWRITE', array ($rewriter, 'rewrite'));
