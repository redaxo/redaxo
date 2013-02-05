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
   * @return rex_be_page_container
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
   * @return rex_be_page_container
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
      if ($new = $obj->getPage()->getSubPage($page[$i])) {
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
    $activePageObj = self::getCurrentPageObject()->getPage();

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
    $page->setIsCorePage(true);
    return $page;
  }

  static public function getLoginPage()
  {
    $page = new rex_be_page('login', 'login');
    $page->setIsCorePage(true);
    $page->setHasNavigation(false);
    return $page;
  }

  static public function appendLoggedInPages()
  {
    $profile = new rex_be_page('profile', rex_i18n::msg('profile'));
    $profile->setIsCorePage(true);
    self::$pages['profile'] = $profile;

    $credits = new rex_be_page('credits', rex_i18n::msg('credits'));
    $credits->setIsCorePage(true);
    self::$pages['credits'] = $credits;

    $addon = new rex_be_page('packages', rex_i18n::msg('addons'));
    $addon->setIsCorePage(true);
    $addon->setRequiredPermissions('isAdmin');
    self::$pages['packages'] = new rex_be_page_main('system', $addon);
    self::$pages['packages']->setPrio(60);

    $settings = new rex_be_page('settings', rex_i18n::msg('main_preferences'));

    $languages = new rex_be_page('lang', rex_i18n::msg('languages'));

    $syslog = new rex_be_page('log', rex_i18n::msg('syslog'));

    $phpinfo = new rex_be_page('phpinfo', 'phpinfo');
    $phpinfo->setHidden(true);
    $phpinfo->setHasLayout(false);

    $mainSpecials = new rex_be_page('system', rex_i18n::msg('system'));
    $mainSpecials->setIsCorePage(true);
    $mainSpecials->setRequiredPermissions('isAdmin');
    $mainSpecials->addSubPage($settings);
    $mainSpecials->addSubPage($languages);
    $mainSpecials->addSubPage($syslog);
    $mainSpecials->addSubPage($phpinfo);
    self::$pages['system'] = new rex_be_page_main('system', $mainSpecials);
    self::$pages['system']->setPrio(70);
  }

  static public function appendAddonPages()
  {
    $addPageProperties = function (rex_be_page_container $page, array $properties, rex_package $package) use (&$addPageProperties) {
      foreach ($properties as $key => $value) {
        if ($key == 'path' || $key == 'subPath') {
          if (file_exists($path = $package->getPath($value))) {
            $value = $path;
          }
        }
        $page->_set($key, $value);
      }
      $page = $page->getPage();
      if (isset($properties['subpages']) && is_array($properties['subpages'])) {
        foreach ($properties['subpages'] as $key => $subProperties) {
          if (isset($subProperties['title'])) {
            $subPage = new rex_be_page($key, $subProperties['title']);
            $page->addSubPage($subPage);
            $addPageProperties($subPage, $subProperties, $package);
          }
        }
      }
    };

    $addons = rex::isSafeMode() ? rex_addon::getSetupAddons() : rex_addon::getAvailableAddons();
    foreach ($addons as $addonKey => $addon) {
      $page = $addon->getProperty('page');
      $mainPage = null;
      if (!$page instanceof rex_be_page_container) {
        $navi = $addon->getProperty('navigation');
        if (isset($navi['title'])) {
          $page = new rex_be_page($addonKey, $navi['title']);
          $mainPage = new rex_be_page_main('addons', $page);
          $addPageProperties($mainPage, $navi, $addon);
        }
      } elseif ($page instanceof rex_be_page_main) {
        $mainPage = $page;
        $page = $mainPage->getPage();
      } else {
        $mainPage = new rex_be_page_main('addons', $page);
      }

      if ($mainPage) {
        self::$pages[$mainPage->getPage()->getKey()] = $mainPage;
      }

      if (is_array($ps = $addon->getProperty('pages'))) {
        foreach ($ps as $p) {
          if ($p instanceof rex_be_page_main) {
            self::$pages[$p->getPage()->getKey()] = $p;
            if (!$p->getPage()->hasPath()) {
              $p->getPage()->setPath($addon->getPath('pages/index.php'));
            }
          } elseif ($p instanceof rex_be_page && $mainPage) {
            $mainPage->getPage()->addSubPage($p);
          }
        }
      }

      // handle plugins
      $plugins = rex::isSafeMode() ? $addon->getSystemPlugins() : $addon->getAvailablePlugins();
      foreach ($plugins as $pluginKey => $plugin) {
        $pluginPage = $plugin->getProperty('page');
        if (!$pluginPage instanceof rex_be_page_container) {
          $pluginPage = null;
          $navi = $plugin->getProperty('navigation');
          if (isset($navi['title'])) {
            $pluginPage = new rex_be_page($pluginKey, $navi['title']);
            $addPageProperties($pluginPage, $navi, $plugin);
          }
        } elseif ($pluginPage instanceof rex_be_page_main) {
          self::$pages[$pluginPage->getPage()->getKey()] = $pluginPage;
          if (!$pluginPage->getPage()->hasPath()) {
            $pluginPage->getPage()->setPath($plugin->getPath('pages/index.php'));
          }
          $pluginPage = null;
        }
        if ($mainPage && $pluginPage) {
          if (!$pluginPage->hasSubPath()) {
            $pluginPage->setSubPath($plugin->getPath('pages/index.php'));
          }
          $mainPage->getPage()->addSubPage($pluginPage);
        }

        if (is_array($ps = $plugin->getProperty('pages'))) {
          foreach ($ps as $p) {
            if ($p instanceof rex_be_page_main) {
              self::$pages[$p->getPage()->getKey()] = $p;
              if (!$p->getPage()->hasPath()) {
                $p->getPage()->setPath($plugin->getPath('pages/index.php'));
              }
            } elseif ($p instanceof rex_be_page && $mainPage) {
              if (!$p->hasSubPath()) {
                $p->setSubPath($plugin->getPath('pages/index.php'));
              }
              $mainPage->getPage()->addSubPage($p);
            }
          }
        }
      }
    }
  }

  static public function checkPage(rex_user $user)
  {
    // --- page pruefen und benoetigte rechte checken
    if (!($p = self::getCurrentPageObject()) ||
      (($p = $p->getPage()) && !$p->checkPermission($user))
    ) {
      // --- fallback zur user startpage -> rechte checken
      $page = $user->getStartPage();
      if (!($p = self::getPageObject($page)) ||
          (($p = $p->getPage()) && !$p->checkPermission($user))
      ) {
        // --- fallback zur system startpage -> rechte checken
        $page = rex::getProperty('start_page');
        if (!($p = self::getPageObject($page)) ||
            (($p = $p->getPage()) && !$p->checkPermission($user))
        ) {
          // --- fallback zur profile page
          $page = 'profile';
        }
      }
      rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
      rex_response::sendRedirect('index.php?page=' . $page);
    }
  }

  /**
   * Includes the current page. A page may be provided by the core, an addon or plugin.
   */
  static public function includeCurrentPage()
  {
    $_activePageObj = self::getCurrentPageObject()->getPage();
    $page = self::getCurrentPagePart(1);
    $_mainPageObj = self::getPageObject($page)->getPage();

    if (rex_request::isPJAXRequest() && !rex_request::isPJAXContainer('#rex-page')) {
      // non-core pjax containers should not have a layout.
      // they render their whole response on their own
      $_activePageObj->setHasLayout(false);
    }

    require rex_path::core('layout/top.php');

    $path = '';
    if ($_activePageObj->hasPath()) {
      $path = $_activePageObj->getPath();
    }

    if ($path != '') {
      // If page has a new/overwritten path
      $pattern = '@' . preg_quote(rex_path::src('addons/'), '@') . '([^/\\\]+)(?:[/\\\]plugins[/\\\]([^/\\\]+))?@';
      if (preg_match($pattern, $path, $matches)) {
        $package = rex_addon::get($matches[1]);
        if (isset($matches[2])) {
          $package = $package->getPlugin($matches[2]);
        }
        $package->includeFile(str_replace($package->getPath(), '', $path));
      } else {
        require $path;
      }
    } elseif ($_mainPageObj->isCorePage()) {
      // Core Page
      require rex_path::core('pages/' . $page . '.php');
    } else {
      // Addon Page
      rex_addon::get($page)->includeFile('pages/index.php');
    }

    require rex_path::core('layout/bottom.php');
  }
}
