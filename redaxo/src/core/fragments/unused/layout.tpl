<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->i18n('htmllang'); ?>" lang="<?php echo $this->i18n('htmllang'); ?>">
<head>
  <title><?php echo $this->pageTitle ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Content-Language" content="<?php echo $this->i18n('htmllang'); ?>" />
  <!-- jQuery immer nach den Stylesheets! -->
  <script src="<?php echo rex_path::assets('jquery.min.js'); ?>" type="text/javascript"></script>
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
<body <?php echo $this->bodyAttr; ?>>
<div id="rex-website">
  <div id="rex-header">
    <p class="rex-header-top"><a href="<?php echo rex_path::frontendController() ?>" onclick="window.open(this.href); return false"><?php echo $this->config('SERVERNAME') ?></a></p>
  </div>

  <div id="rex-navi-logout"><?php echo $this->logout ?></div>
  <div id="rex-navi-main"><?php echo $this->navigation ?></div>

  <div id="rex-wrapper">
    <div id="rex-wrapper2">

    <?php echo $this->rexDecoratedContent; ?>

      </div>
    <!-- *** OUTPUT OF CONTENT - END *** -->

    	<div id="sidebar"></div>

    </div>
  </div><?php /* END #rex-wrapper - nicht als HTML Kommentar setzen, sonst Bug im IE */ ?>

  <div id="rex-footer">
    <div id="rex-navi-footer">
      <ul class="rex-navi"><li class="rex-navi-first"><a href="#rex-header">&#94;</a></li><li><a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">yakamara.de</a></li><li><a href="http://www.redaxo.org" onclick="window.open(this.href); return false;">redaxo.org</a></li><li><a href="http://www.redaxo.org/de/forum/" onclick="window.open(this.href); return false;">redaxo.org/de/forum/</a></li><?php if(rex::getUser()) echo '<li><a href="index.php?page=credits">'.$this->i18n('credits').'</a></li>'; ?></ul>
      <p id="rex-scripttime"><!--DYN--><?php echo rex_formatter :: format(memory_get_peak_usage(), 'filesize', array(3)); ?> | <?php echo rex::getProperty('timer')->getFormattedTime() ?> sec | <?php echo rex_formatter :: format(time(), 'strftime', 'date'); ?><!--/DYN--></p>
    </div>
  </div>

  <div id="rex-extra"></div>

  </div><!-- END #rex-website -->
</body>
</html>