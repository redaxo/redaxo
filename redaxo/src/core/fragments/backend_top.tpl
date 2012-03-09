<!doctype html>
<html lang="<?php echo $this->i18n('htmllang'); ?>">
<head>
  <meta charset="utf-8">
  
  <title><?php echo $this->pageTitle ?></title>
  <meta http-equiv="Content-Language" content="<?php echo $this->i18n('htmllang'); ?>" />

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <script src="<?php echo rex_path::assets('jquery.min.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_path::assets('jquery-ui.custom.min.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_path::assets('jquery-pjax.min.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_path::assets('standard.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_path::assets('sha1.js'); ?>" type="text/javascript"></script>
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
<body<?php echo $this->bodyAttr; ?>>

<div id="rex-page">