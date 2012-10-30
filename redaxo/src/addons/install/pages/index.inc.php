<?php

$page = rex_be_controller::getCurrentPagePart(1);
$subpage = rex_be_controller::getCurrentPagePart(2, '');
$subsubpage = rex_be_controller::getCurrentPagePart(3);

if ($subpage != 'settings' && !$this->getPlugin($subpage)->isAvailable()) {
  foreach ($this->getAvailablePlugins() as $plugin) {
    header('Location: ' . rex_url::backendPage('install/' . $plugin->getName()));
    exit;
  }
}

echo rex_view::title($this->i18n('name'));

if ($subpage == 'settings') {
  include $this->getBasePath('pages/settings.inc.php');
} elseif ($this->getPlugin($subpage)->isAvailable()) {
  $this->getPlugin($subpage)->includeFile('pages/index.inc.php');
} else {
  echo rex_view::warning($this->i18n('no_plugins'));
}
