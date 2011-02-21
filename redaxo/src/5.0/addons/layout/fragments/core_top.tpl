<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->i18n('htmllang'); ?>" lang="<?php echo $this->i18n('htmllang'); ?>">
<head>
  <title><?php echo $this->pageTitle ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Content-Language" content="<?php echo $this->i18n('htmllang'); ?>" />
  <!-- jQuery immer nach den Stylesheets! -->
  <script src="<?php echo rex_path::assets('jquery.min.js', true); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_path::assets('standard.js', true); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_path::assets('sha1.js', true); ?>" type="text/javascript"></script>
  <script type="text/javascript">
  <!--
  var redaxo = true;

  // jQuery is now removed from the $ namespace
  // to use the $ shorthand, use (function($){ ... })(jQuery);
  // and for the onload handler: jQuery(function($){ ... });
  jQuery.noConflict();
  //-->
  </script>

  <?php echo $this->pageHeader ?>
</head>
<body <?php echo $this->bodyAttr; ?>>
<div id="rex-page">

  <div class="rex-header" id="rex-header">
    <p class="rex-server"><a href="<?php echo rex_path::frontendController() ?>" onclick="window.open(this.href); return false"><?php echo $this->config('SERVERNAME') ?></a></p>
  
	  <div class="rex-navi-logout"><?php echo $this->logout ?></div>
  </div>

  <div class="rex-nav" id="rex-navi-main"><?php echo $this->navigation ?></div>

  <div id="rex-main">