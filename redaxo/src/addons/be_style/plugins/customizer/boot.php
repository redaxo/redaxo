<?php

/**
 * REDAXO customizer
 *
 * Codemirror by : http://codemirror.net/
 * Marijn Haverbeke <marijnh@gmail.com>
 */

if (rex::isBackend())
{	
	$curDir = __DIR__;
	require_once $curDir . '/functions/function_customizer.php';
	
	$config = rex_plugin::get('be_style', 'customizer')->getConfig();
	
	rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
	
	if($config['codemirror'])
	{
		rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/codemirror.css'));
		rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/theme/'.$config['codemirror_theme'].'.css'));
		rex_view::setJsProperty('customizer_codemirror_defaulttheme',$config['codemirror_theme']);
		rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/codemirror-compressed.js'));
		rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/rex-init.js'));
	}
	
	// if($config['labelcolor'] != '')
	// {
		// $add .= ("\n".'<style>#rex-navi-logout {  border-bottom: 10px solid ' . htmlspecialchars($config['labelcolor']). '; }</style>');
	// }
	
	// if($config['showlink'])
	// {
		// rex_extension::register('OUTPUT_FILTER', 'rex_be_style_customizer_extra');
	// }
	
	// if($config['textarea'] || $config['liquid'] || $config['nav_flyout'])
	// {
		// rex_extension::register('PAGE_BODY_ATTR', 'rex_be_style_customizer_body');
	// }
}