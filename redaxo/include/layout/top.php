<?php

/**
 * Layout Kopf des Backends
 * @package redaxo4
 * @version svn:$Id$
 */
 
$popups_arr = array('linkmap', 'mediapool');

$page_title = $REX['SERVERNAME'];

if(!isset($page_name))
{
  $curPage = $REX['PAGES'][$REX['PAGE']]->getPage();
  $page_name = $curPage->getTitle();
}
  
if ($page_name != '')
  $page_title .= ' - ' . $page_name;

$body_attr = array();
$body_id = str_replace('_', '-', $REX["PAGE"]);

if (in_array($body_id, $popups_arr))
  $body_attr["class"] = array('rex-popup'.$body_id);

$body_attr["id"] = array('rex-page-'.$body_id);
$body_attr["onunload"] = array('closeAll();');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $I18N->msg('htmllang'); ?>" lang="<?php echo $I18N->msg('htmllang'); ?>">
<head>
  <title><?php echo htmlspecialchars($page_title) ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $I18N->msg('htmlcharset'); ?>" />
  <meta http-equiv="Content-Language" content="<?php echo $I18N->msg('htmllang'); ?>" />
  <link rel="stylesheet" type="text/css" href="media/css_import.css" media="screen, projection, print" />
  <!--[if lte IE 7]>
		<link rel="stylesheet" href="media/css_ie_lte_7.css" type="text/css" media="screen, projection, print" />
	<![endif]-->
			
	<!--[if IE 7]>
		<link rel="stylesheet" href="media/css_ie_7.css" type="text/css" media="screen, projection, print" />
	<![endif]-->
	
	<!--[if lte IE 6]>
		<link rel="stylesheet" href="media/css_ie_lte_6.css" type="text/css" media="screen, projection, print" />
	<![endif]-->

  <!-- jQuery immer nach den Stylesheets! -->
  <script src="media/jquery.min.js" type="text/javascript"></script>
  <script src="media/standard.js" type="text/javascript"></script>
  <script type="text/javascript">
  <!--
  var redaxo = true;

  // jQuery is now removed from the $ namespace
  // to use the $ shorthand, use (function($){ ... })(jQuery);
  // and for the onload handler: jQuery(function($){ ... });
  jQuery.noConflict();
  //-->
  </script>
<?php

// ----- EXTENSION POINT
echo rex_register_extension_point('PAGE_HEADER', '' );
$body_attr = rex_register_extension_point('PAGE_BODY_ATTR', $body_attr );

$body = "";
foreach($body_attr as $k => $v){
	$body .= $k.'="';
	if(is_array($v))
		$body .= implode(" ",$v);
	$body .= '" ';
}  

?>
</head>
<body <?php echo $body; ?>>
<div id="rex-website">
<div id="rex-header">

  <p class="rex-header-top"><a href="../index.php" onclick="window.open(this.href);"><?php echo htmlspecialchars($REX['SERVERNAME']); ?></a></p>

</div>

<div id="rex-navi-logout"><?php
  
if ($REX['USER'] && !$REX["PAGE_NO_NAVI"])
{
  $accesskey = 1;
  $user_name = $REX['USER']->getValue('name') != '' ? $REX['USER']->getValue('name') : $REX['USER']->getValue('login');
  echo '<ul class="rex-logout"><li class="rex-navi-first"><span>' . $I18N->msg('logged_in_as') . ' '. htmlspecialchars($user_name) .'</span></li><li><a href="index.php?page=profile">' . $I18N->msg('profile_title') . '</a></li><li><a href="index.php?rex_logout=1"'. rex_accesskey($I18N->msg('logout'), $REX['ACKEY']['LOGOUT']) .'>' . $I18N->msg('logout') . '</a></li></ul>' . "\n";
}else if(!$REX["PAGE_NO_NAVI"])
{
  echo '<p class="rex-logout">' . $I18N->msg('logged_out') . '</p>';
}else
{
  echo '<p class="rex-logout">&nbsp;</p>';
}
  
?></div>

  <div id="rex-navi-main">
<?php

if ($REX['USER'] && !$REX["PAGE_NO_NAVI"])
{
	$n = rex_be_navigation::factory();
	
	foreach($REX['USER']->pages as $p => $pageContainer)
  {
		$p = strtolower($p);
    if(rex_be_main_page::isValid($pageContainer))
    {
      $pageObj =& $pageContainer->getPage();
      $pageObj->setItemAttr('id', 'rex-navi-page-'.strtolower(preg_replace('/[^a-zA-Z0-9\-_]*/', '', $p)));
      
      if(!$pageContainer->getBlock())
        $pageContainer->setBlock('addons');
        
      if(!$pageObj->getHref())
        $pageObj->setHref('index.php?page='.$p);
      /*
       if(isset ($REX['ACKEY']['ADDON'][$page]))
        $item['extra'] = rex_accesskey($name, $REX['ACKEY']['ADDON'][$page]);
      else 
        $item['extra'] = rex_accesskey($pageArr['title'], $accesskey++);
      */
        
      $pageObj->setLinkAttr('tabindex', rex_tabindex(false));
      $n->addPage($pageContainer);
    }
  }
	
  $n->setActiveElements();
  echo $n->getNavigation();
}

?>
</div>


<div id="rex-wrapper">
<div id="rex-wrapper2">