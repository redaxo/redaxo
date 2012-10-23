<?php

class rex_be_controller
{
  static public function getPageTitle()
  {
    $pages = rex::getProperty('pages');
    $curPage = $pages[rex::getProperty('page')]->getPage();

    $activePageObj = $curPage;
    if ($subpage = $curPage->getActiveSubPage()) {
      $activePageObj = $subpage;
    }

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
    $page = new rex_be_page(rex_i18n::msg('setup'));
    $page->setIsCorePage(true);
    return $page;
  }

  static public function getLoginPage()
  {
    $page = new rex_be_page('login');
    $page->setIsCorePage(true);
    $page->setHasNavigation(false);
    return $page;
  }

  static public function getLoggedInPages()
  {
    $pages = array();

    $profile = new rex_be_page(rex_i18n::msg('profile'));
    $profile->setIsCorePage(true);
    $pages['profile'] = $profile;

    $credits = new rex_be_page(rex_i18n::msg('credits'));
    $credits->setIsCorePage(true);
    $pages['credits'] = $credits;

    $addon = new rex_be_page(rex_i18n::msg('addons'), array('page' => 'addon'));
    $addon->setIsCorePage(true);
    $addon->setRequiredPermissions('isAdmin');
    $pages['addon'] = new rex_be_page_main('system', $addon);
    $pages['addon']->setPrio(60);

    $settings = new rex_be_page(rex_i18n::msg('main_preferences'), array('page' => 'system', 'subpage' => ''));
    $settings->setIsCorePage(true);
    $settings->setRequiredPermissions('isAdmin');
    $settings->setHref('index.php?page=system&subpage=');

    $languages = new rex_be_page(rex_i18n::msg('languages'), array('page' => 'system', 'subpage' => 'lang'));
    $languages->setIsCorePage(true);
    $languages->setRequiredPermissions('isAdmin');
    $languages->setHref('index.php?page=system&subpage=lang');

    $syslog = new rex_be_page(rex_i18n::msg('syslog'), array('page' => 'system', 'subpage' => 'log'));
    $syslog->setIsCorePage(true);
    $syslog->setRequiredPermissions('isAdmin');
    $syslog->setHref('index.php?page=system&subpage=log');

    $phpinfo = new rex_be_page(rex_i18n::msg('phpinfo'), array('page' => 'system', 'subpage' => 'phpinfo'));
    $phpinfo->setIsCorePage(true);
    $phpinfo->setRequiredPermissions('isAdmin');
    $phpinfo->setHidden(true);
    $phpinfo->setHasLayout(false);
    $phpinfo->setHref('index.php?page=system&subpage=phpinfo');

    $mainSpecials = new rex_be_page(rex_i18n::msg('system'), array('page' => 'system'));
    $mainSpecials->setIsCorePage(true);
    $mainSpecials->setRequiredPermissions('isAdmin');
    $mainSpecials->addSubPage($settings);
    $mainSpecials->addSubPage($languages);
    $mainSpecials->addSubPage($syslog);
    $mainSpecials->addSubPage($phpinfo);
    $pages['system'] = new rex_be_page_main('system', $mainSpecials);
    $pages['system']->setPrio(70);

    $pages['addon'] = new rex_be_page_main('system', $addon);
    $pages['addon']->setPrio(60);

    return $pages;
  }

  static public function appendAddonPages(array $pages)
  {
    $addons = rex::isSafeMode() ? rex_addon::getSetupAddons() : rex_addon::getAvailableAddons();
    foreach ($addons as $addonName => $addon) {
      $page  = $addon->getProperty('page', null);
      $title = $addon->getProperty('name', '');
      $href  = $addon->getProperty('link',  'index.php?page=' . $addonName);
      $perm  = $addon->getProperty('perm', '');

      // prepare addons root-page
      $addonPage = null;
      if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission(rex::getUser())) {
        $addonPage = $page;
      } elseif ($perm == '' || rex::getUser()->hasPerm($perm)) {
        if ($title != '') {
          $addonPage = new rex_be_page($title, array('page' => $addonName));
          $addonPage->setHref($href);
        }
      }

      if ($addonPage) {
        // adds be_page's
        foreach ($addon->getProperty('pages', array()) as $s) {
          if (is_array($s)) {
            if (!isset($s[2]) || rex::getUser()->hasPerm($s[2])) {
              $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
              $subPage->setHref('index.php?page=' . $addonName . '&subpage=' . $s[0]);
              $addonPage->addSubPage($subPage);
            }
          } elseif ($s instanceof rex_be_page_main) {
            $p = $s->getPage();
            $pages[$addonName . '_' . $p->getTitle()] = $s;
          } elseif ($s instanceof rex_be_page && $addonPage) {
            $addonPage->addSubPage($s);
          }
        }
      }

      // handle plugins
      $plugins = rex::isSafeMode() ? $addon->getSystemPlugins() : $addon->getAvailablePlugins();
      foreach ($plugins as $pluginName => $plugin) {
        $page  = $plugin->getProperty('page', null);

        $title = $plugin->getProperty('name', '');
        $href  = $plugin->getProperty('link',  'index.php?page=' . $addonName . '&subpage=' . $pluginName);
        $perm  = $plugin->getProperty('perm', '');

        // prepare plugins root-page
        $pluginPage = null;
        if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission(rex::getUser())) {
          $pluginPage = $page;
        } elseif ($perm == '' || rex::getUser()->hasPerm($perm)) {
          if ($title != '') {
            $pluginPage = new rex_be_page($title, array('page' => $addonName, 'subpage' => $pluginName));
            $pluginPage->setHref($href);
          }
        }

        // add plugin-be_page's to addon
        foreach ($plugin->getProperty('pages', array()) as $s) {
          if (is_array($s) && $addonPage) {
            if (!isset($s[2]) || rex::getUser()->hasPerm($s[2])) {
              $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
              $subPage->setHref('index.php?page=' . $addonName . '&subpage=' . $s[0]);
              $addonPage->addSubPage($subPage);
            }
          } elseif ($s instanceof rex_be_page_main) {
            $p = $s->getPage();
            $pages[$addonName . '_' . $pluginName . '_' . $p->getTitle()] = $s;
          } elseif ($s instanceof rex_be_page && $addonPage) {
            $addonPage->addSubPage($s);
          }
        }

        if ($pluginPage) {
          if ($pluginPage instanceof rex_be_page_main) {
            if (!$pluginPage->getPage()->hasPath()) {
              $pagePath = rex_path::plugin($addonName, $pluginName, 'pages/index.inc.php');
              $pluginPage->getPage()->setPath($pagePath);
            }
            $pages[$pluginName] = $pluginPage;
          } else {
            // "navigation" adds attributes to the plugin-root page
            $navProperties = $plugin->getProperty('navigation', array());
            // if there are some navigation attributes set, create a main page and apply attributes to it
            if (count($navProperties) > 0) {
              $mainPluginPage = new rex_be_page_main($addonName, $pluginPage);
              foreach ($navProperties as $key => $value) {
                $mainPluginPage->_set($key, $value);
              }
              $pages[$addonName . '_' . $pluginName] = $mainPluginPage;
            }
            // if no navigation attributes can be found, we add the pluginPage as subPage of the addon
            elseif ($addonPage) {
              $addonPage->addSubPage($pluginPage);
            }
          }
        }
      }

      if ($addonPage instanceof rex_be_page_main) {
        // addonPage was defined as a main-page itself, so we only need to add it to REX
        $pages[$addonName] = $addonPage;
      } else {
        // wrap the be_page into a main_page
        $mainAddonPage = null;
        if ($addonPage) {
          $mainAddonPage = new rex_be_page_main('addons', $addonPage);

          // "navigation" adds attributes to the addon-root page
          foreach ($addon->getProperty('navigation', array()) as $key => $value) {
            $mainAddonPage->_set($key, $value);
          }
          $pages[$addonName] = $mainAddonPage;
        }
      }
    }

    return $pages;
  }

  static public function checkPage($page, array $pages, rex_user $user)
  {
    // --- page pruefen und benoetigte rechte checken
    if (!isset($pages[$page]) ||
        (($p = $pages[$page]->getPage()) && !$p->checkPermission($user))
    ) {
      // --- fallback zur user startpage -> rechte checken
      $page = $user->getStartPage();
      if (!isset($pages[$page]) ||
          (($p = $pages[$page]->getPage()) && !$p->checkPermission($user))
      ) {
        // --- fallback zur system startpage -> rechte checken
        $page = rex::getProperty('start_page');
        if (!isset($pages[$page]) ||
            (($p = $pages[$page]->getPage()) && !$p->checkPermission($user))
        ) {
          // --- user hat keine rechte innerhalb der fallback-kette
          return null;
        }
      }
    }

    return $page;
  }

  /**
   * Includes the given page. A page may be provided by the core, an addon or plugin.
   *
   * @param rex_be_page $_activePageObj The actual page to activate
   * @param rex_be_page $_mainPageObj   The main page. For root pages this is the same object as $_activePageObj
   * @param string      $page           The name of the page
   */
  static public function includePage(rex_be_page $_activePageObj, rex_be_page $_mainPageObj, $page)
  {
    if (rex_request::isPJAXRequest() && !rex_request::isPJAXContainer('#rex-page')) {
      // non-core pjax containers should not have a layout.
      // they render their whole response on their own
      $_activePageObj->setHasLayout(false);
    }

    if($_activePageObj->hasLayout()) {
      require rex_path::core('layout/top.php');
    }

    $path = '';
    if ($_activePageObj->hasPath()) {
      $path = $_activePageObj->getPath();
    } elseif ($_mainPageObj->hasPath()) {
      $path = $_mainPageObj->getPath();
    }

    if ($path != '') {
      // If page has a new/overwritten path
      $pattern = '@' . preg_quote(rex_path::src('addons/'), '@') . '([^/\\\]+)(?:[/\\\]plugins[/\\\]([^/\\\]+))?@';
      if (preg_match($pattern, $path, $matches)) {
        $package = rex_addon::get($matches[1]);
        if (isset($matches[2])) {
          $package = $package->getPlugin($matches[2]);
        }
        rex_package_manager::includeFile($package, str_replace($package->getBasePath(), '', $path));
      } else {
        require $path;
      }
    } elseif ($_mainPageObj->isCorePage()) {
      // Core Page
      require rex_path::core('pages/' . $page . '.inc.php');
    } else {
      // Addon Page
      rex_addon_manager::includeFile(rex_addon::get($page), 'pages/index.inc.php');
    }
    
    if($_activePageObj->hasLayout()) {
      require rex_path::core('layout/bottom.php');
    }
  }
}
