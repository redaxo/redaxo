<?php

class rex_be_controller
{
  static private
    $page,
    $pageParts = array(),
    $pageObject,
    $pages = array();

  static public function setCurrentPage($page)
  {
    self::$page = trim($page, '/ ');
    self::$pageParts = explode('/', self::$page);
    self::$pageObject = null;
  }

  static public function getCurrentPage()
  {
    return self::$page;
  }

  static public function getCurrentPagePart($part = null, $default = null)
  {
    if ($part === null) {
      return self::$pageParts;
    }
    $part -= 1;
    return isset(self::$pageParts[$part]) ? self::$pageParts[$part] : $default;
  }

  /**
   * @return rex_be_page
   */
  static public function getCurrentPageObject()
  {
    if (!self::$pageObject) {
      self::$pageObject = self::getPageObject(self::getCurrentPage());
    }
    return self::$pageObject;
  }

  /**
   * @param string $page
   * @return rex_be_page
   */
  static public function getPageObject($page)
  {
    if (!is_array($page)) {
      $page = explode('/', $page);
    }
    if (!isset($page[0]) || !isset(self::$pages[$page[0]])) {
      return null;
    }
    $obj = self::$pages[$page[0]];
    for ($i = 1, $count = count($page); $i < $count; ++$i) {
      if ($new = $obj->getSubPage($page[$i])) {
        $obj = $new;
      } else {
        return null;
      }
    }
    return $obj;
  }

  static public function getPages()
  {
    return self::$pages;
  }

  static public function setPages(array $pages)
  {
    self::$pages = $pages;
  }

  static public function getPageTitle()
  {
    $activePageObj = self::getCurrentPageObject();

    $page_name = $activePageObj->getTitle();
    $page_title = rex::getProperty('servername');

    if ($page_name != '') {
      if ($page_title != '') {
        $page_title .= ' - ';
      }
      $page_title .= $page_name;
    }

    return $page_title;
  }

  static public function getSetupPage()
  {
    $page = new rex_be_page('setup', rex_i18n::msg('setup'));
    $page->setPath(rex_path::core('pages/setup.php'));
    return $page;
  }

  static public function getLoginPage()
  {
    $page = new rex_be_page('login', 'login');
    $page->setPath(rex_path::core('pages/login.php'));
    $page->setHasNavigation(false);
    return $page;
  }

  static public function appendLoggedInPages()
  {
    $profile = new rex_be_page('profile', rex_i18n::msg('profile'));
    $profile->setPath(rex_path::core('pages/profile.php'));
    self::$pages['profile'] = $profile;

    $credits = new rex_be_page('credits', rex_i18n::msg('credits'));
    $credits->setPath(rex_path::core('pages/credits.php'));
    self::$pages['credits'] = $credits;

    $packages = new rex_be_page_main('system', 'packages', rex_i18n::msg('addons'));
    $packages->setPath(rex_path::core('pages/packages.php'));
    $packages->setRequiredPermissions('isAdmin');
    $packages->setPrio(60);
    self::$pages['packages'] = $packages;

    $system = new rex_be_page_main('system', 'system', rex_i18n::msg('system'));
    $system->setPath(rex_path::core('pages/system.php'));
    $system->setRequiredPermissions('isAdmin');
    $system->setPrio(70);
    $system->addSubPage(new rex_be_page('settings', rex_i18n::msg('main_preferences')));
    $system->addSubPage(new rex_be_page('lang', rex_i18n::msg('languages')));
    $system->addSubPage(new rex_be_page('log', rex_i18n::msg('syslog')));
    $phpinfo = new rex_be_page('phpinfo', 'phpinfo');
    $phpinfo->setHidden(true);
    $phpinfo->setHasLayout(false);
    $system->addSubPage($phpinfo);
    self::$pages['system'] = $system;
  }

  static public function appendPackagePages()
  {
    $addons = rex::isSafeMode() ? rex_addon::getSetupAddons() : rex_addon::getAvailableAddons();
    foreach ($addons as $addonKey => $addon) {
      $mainPage = self::getPackagePage($addon, true);

      self::addPackagePages($addon, $mainPage);

      // handle plugins
      $plugins = rex::isSafeMode() ? $addon->getSystemPlugins() : $addon->getAvailablePlugins();
      foreach ($plugins as $pluginKey => $plugin) {
        $page = self::getPackagePage($plugin, false);

        if ($mainPage && $page && !$page instanceof rex_be_page_main) {
          if (!$page->hasSubPath()) {
            $page->setSubPath($plugin->getPath('pages/index.php'));
          }
          $mainPage->addSubPage($page);
        }

        self::addPackagePages($plugin, $mainPage);
      }
    }
  }

  /**
   * @param rex_be_page $page
   * @param rex_package $package
   * @param string      $prefix
   */
  static private function pageSetSubPaths(rex_be_page $page, rex_package $package, $prefix = '')
  {
    foreach ($page->getSubPages() as $subpage) {
      if (!$subpage->hasSubPath()) {
        $subpage->setSubPath($package->getPath('pages/' . $prefix . $subpage->getKey() . '.php'));
      }
      self::pageSetSubPaths($subpage, $package, $prefix . $subpage->getKey() . '.');
    }
  }

  /**
   * @param rex_be_page $page
   * @param array       $properties
   * @param rex_package $package
   */
  static private function pageAddProperties(rex_be_page $page, array $properties, rex_package $package)
  {
    foreach ($properties as $key => $value) {
      if ($key == 'path' || $key == 'subPath') {
        if (file_exists($path = $package->getPath($value))) {
          $value = $path;
        }
      }
      $page->_set($key, $value);
    }
    if (isset($properties['subpages']) && is_array($properties['subpages'])) {
      foreach ($properties['subpages'] as $key => $subProperties) {
        if (isset($subProperties['title'])) {
          $subPage = new rex_be_page($key, $subProperties['title']);
          $page->addSubPage($subPage);
          self::pageAddProperties($subPage, $subProperties, $package);
        }
      }
    }
  }

  /**
   * @param rex_package $package
   * @param boolean     $createMainPage
   * @return null|rex_be_page
   */
  static private function getPackagePage(rex_package $package, $createMainPage)
  {
    $page = $package->getProperty('page');
    if (!$page instanceof rex_be_page) {
      $navi = $package->getProperty('navigation');
      if (isset($navi['title'])) {
        if ($createMainPage) {
          $page = new rex_be_page_main('addons', $package->getName(), $navi['title']);
        } else {
          $page = new rex_be_page($package->getName(), $navi['title']);
        }
        self::pageAddProperties($page, $navi, $package);
      }
    }

    if ($page instanceof rex_be_page_main) {
      if (!$page->hasPath()) {
        $page->setPath($package->getPath('pages/index.php'));
      }
      self::$pages[$page->getKey()] = $page;
    }

    if ($page instanceof rex_be_page) {
      self::pageSetSubPaths($page, $package);
      return $page;
    }
    return null;
  }

  /**
   * @param rex_package      $package
   * @param rex_be_page_main $mainPage
   */
  static private function addPackagePages(rex_package $package, rex_be_page_main $mainPage = null)
  {
    if (is_array($pages = $package->getProperty('pages'))) {
      foreach ($pages as $page) {
        $prefix = $page->getKey() . '.';
        self::pageSetSubPaths($page, $package, $prefix);
        if ($page instanceof rex_be_page_main) {
          self::$pages[$page->getKey()] = $page;
          if (!$page->hasPath()) {
            $page->setPath($package->getPath('pages/' . $prefix . 'php'));
          }
        } elseif ($page instanceof rex_be_page && $mainPage) {
          if (!$page->hasSubPath()) {
            $page->setSubPath($package->getPath('pages/' . $prefix . 'php'));
          }
          $mainPage->addSubPage($page);
        }
      }
    }
  }

  static public function checkPage(rex_user $user)
  {
    // --- page pruefen und benoetigte rechte checken
    if (!($p = self::getCurrentPageObject()) || !$p->checkPermission($user)) {
      // --- fallback zur user startpage -> rechte checken
      $page = $user->getStartPage();
      if (!($p = self::getPageObject($page)) || !$p->checkPermission($user)) {
        // --- fallback zur system startpage -> rechte checken
        $page = rex::getProperty('start_page');
        if (!($p = self::getPageObject($page)) || !$p->checkPermission($user)) {
          // --- fallback zur profile page
          $page = 'profile';
        }
      }
      rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
      rex_response::sendRedirect(rex_url::backendPage($page));
    }
  }

  /**
   * Includes the current page. A page may be provided by the core, an addon or plugin.
   */
  static public function includeCurrentPage()
  {
    $currentPage = self::getCurrentPageObject();

    if (rex_request::isPJAXRequest() && !rex_request::isPJAXContainer('#rex-page')) {
      // non-core pjax containers should not have a layout.
      // they render their whole response on their own
      $currentPage->setHasLayout(false);
    }

    require rex_path::core('layout/top.php');

    $path = $currentPage->getPath();
    $pattern = '@' . preg_quote(rex_path::src('addons/'), '@') . '([^/\\\]+)(?:[/\\\]plugins[/\\\]([^/\\\]+))?@';
    if (preg_match($pattern, $path, $matches)) {
      $package = rex_addon::get($matches[1]);
      if (isset($matches[2])) {
        $package = $package->getPlugin($matches[2]);
      }
      $package->includeFile(str_replace($package->getPath(), '', $path));
    } else {
      include $path;
    }

    require rex_path::core('layout/bottom.php');
  }
}
