<!doctype html>
<html lang="<?php echo  rex_i18n::msg('htmllang'); ?>">
<head>
  <meta charset="utf-8">

  <title><?php echo $this->pageTitle ?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1" />
<?php

  foreach ($this->cssFiles as $media => $files) {
    foreach ($files as $file) {
      echo "\n" . '  <link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $file . '" />';
    }
  }
  echo "\n";
  foreach ($this->jsFiles as $media => $file) {
    echo "\n" . '  <script type="text/javascript" src="' . $file . '"></script>';
  }
?>

  <script type="text/javascript">
  <!--
  var rex = <?php echo $this->jsProperties ?>;
  //-->
  </script>

  <?php echo $this->pageHeader ?>

</head>
<body<?php echo $this->bodyAttr; ?>>

<div id="rex-ajax-loader" style="display: none">Loading...</div>
<div id="rex-page">
