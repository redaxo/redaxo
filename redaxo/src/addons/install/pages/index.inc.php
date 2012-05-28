<?php

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$subsubpage = rex_request('subsubpage', 'string');

if($subpage != 'settings' && !$this->getPlugin($subpage)->isAvailable())
{
  foreach($this->getAvailablePlugins() as $plugin)
  {
    header('Location: index.php?page=install&subpage='. $plugin->getName());
    exit;
  }
}

echo rex_view::title($this->i18n('name'));

if($subpage == 'settings')
{
  include $this->getBasePath('pages/settings.inc.php');
}
elseif($this->getPlugin($subpage)->isAvailable())
{
  $plugin = $this->getPlugin($subpage);
  if($plugin->hasProperty('subpages'))
  {
    $listElements = array();
    foreach($plugin->getProperty('subpages') as $i => $page)
    {
      $n = array();
      $n['title'] = $page[1];
      $n['href'] = 'index.php?page=install&subpage='.$subpage.'&subsubpage='.$page[0];
      if ($page[0] == $subsubpage)
      {
        $n['itemClasses'] = array('rex-active');
        $n['linkClasses'] = array('rex-active');
      }
      $listElements[] = $n;
      /*
      $subsubpages .= sprintf(
        '<li%s><a href="index.php?page=install&subpage=%s&subsubpage=%s"%s>%s</a></li>',
        ($page[0] == $subsubpage ? ' class="rex-active"' : ''),
        $subpage,
        $page[0],
        ($page[0] == $subsubpage ? ' class="rex-active"' : ''),
        $page[1]
      );
      */
    }

    $blocks = array();
    $blocks[] = array(
      'navigation' => $listElements
      );

    $fragment = new rex_fragment();
    $fragment->setVar('type', 'tabsub', false);
    $fragment->setVar('blocks', $blocks, false);
    echo $fragment->parse('navigation.tpl');

//    echo rex_view::toolbar('<div class="rex-content-header-2"><ul>'.$subsubpages.'</ul></div>', 'rex-content-header');
  }

  $plugin->includeFile('pages/index.inc.php');
}
else
{
  echo rex_view::warning($this->i18n('no_plugins'));
}
