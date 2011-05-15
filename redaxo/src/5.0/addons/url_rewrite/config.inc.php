<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
 * @version svn:$Id$
 */

if (rex_core::getProperty('mod_rewrite') !== false)
{

  $rewriter = new rex_urlRewriter_fullnames();
  $rewriter->prepare();

  rex_extension::register('URL_REWRITE', array ($rewriter, 'rewrite'));
}
