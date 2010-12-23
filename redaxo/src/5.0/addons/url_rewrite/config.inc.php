<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

if ($REX['MOD_REWRITE'] !== false)
{

  $rewriter = new rex_urlRewriter_fullnames();
  $rewriter->prepare();

  rex_register_extension('URL_REWRITE', array ($rewriter, 'rewrite'));
}
