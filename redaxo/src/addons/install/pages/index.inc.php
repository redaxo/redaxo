<?php

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$subsubpage = rex_request('subsubpage', 'string');

if(!$subpage || !$this->getPlugin($subpage)->isAvailable())
{
  foreach($this->getAvailablePlugins() as $plugin)
  {
    header('Location: index.php?page=install&subpage='. $plugin->getName());
    exit;
  }
}

echo rex_view::title($this->i18n('name'));

if($subpage && $this->getPlugin($subpage)->isAvailable())
{
  $plugin = $this->getPlugin($subpage);
  if($plugin->hasProperty('subpages'))
  {
    $subsubpages = '';
    foreach($plugin->getProperty('subpages') as $i => $page)
    {
      $subsubpages .= sprintf(
        '<li%s><a href="index.php?page=install&subpage=%s&subsubpage=%s"%s>%s</a></li>',
        ($i == 0 ? ' class="rex-navi-first"' : ''),
        $subpage,
        $page[0],
        ($page[0] == $subsubpage ? ' class="rex-active"' : ''),
        $page[1]
      );
    }
    echo rex_view::toolbar('<div class="rex-content-header-2"><ul>'.$subsubpages.'</ul></div>', 'rex-content-header');
  }

  $plugin->includeFile('pages/index.inc.php');
}
else
{
  echo rex_view::warning($this->i18n('no_plugins'));
}