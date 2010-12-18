<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

if ($REX['MOD_REWRITE'] !== false)
{
  $UrlRewriteBasedir = dirname(__FILE__);
  require_once $UrlRewriteBasedir.'/classes/class.urlrewriter.inc.php';
  
  // --------- configuration
  
  // Modify this line to include the right rewriter
  require_once $UrlRewriteBasedir.'/classes/class.rewrite_fullnames.inc.php';
  
  // --------- end of configuration

  $rewriter = new myUrlRewriter();
  $rewriter->prepare();
  
  rex_register_extension('URL_REWRITE', array ($rewriter, 'rewrite'));
}
