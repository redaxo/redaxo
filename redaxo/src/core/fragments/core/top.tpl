<!doctype html>
<html lang="<?php echo $this->i18n('htmllang'); ?>">
<head>
  <meta charset="utf-8">

  <title><?php echo $this->pageTitle ?></title>
  <meta http-equiv="Content-Language" content="<?php echo $this->i18n('htmllang'); ?>" />

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <script src="<?php echo rex_url::assets('jquery.min.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_url::assets('jquery-ui.custom.min.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_url::assets('jquery-pjax.min.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_url::assets('standard.js'); ?>" type="text/javascript"></script>
  <script src="<?php echo rex_url::assets('sha1.js'); ?>" type="text/javascript"></script>
  <script type="text/javascript">
  <!--
  var redaxo = true;
  //-->
  </script>

  <?php echo $this->pageHeader ?>

</head>
<body<?php echo $this->bodyAttr; ?>>

<div id="rex-ajax-loader" style="display: none">Loading...</div>
<div id="rex-page">
