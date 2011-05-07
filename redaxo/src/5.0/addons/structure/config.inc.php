<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

rex_var::registerVar('rex_var_globals');
rex_var::registerVar('rex_var_article');
rex_var::registerVar('rex_var_category');

require_once dirname(__FILE__). '/functions/function_rex_url.inc.php';