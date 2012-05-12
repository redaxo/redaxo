<?php

class rex_be_controller
{
  public static function appendAddonPage($pages)
  {
    $addons = rex::isSafeMode() ? rex_addon::getSetupAddons() : rex_addon::getAvailableAddons();
    foreach($addons as $addonName => $addon)
    {
      $page  = $addon->getProperty('page', null);
      $title = $addon->getProperty('name', '');
      $href  = $addon->getProperty('link',  'index.php?page='. $addonName);
      $perm  = $addon->getProperty('perm', '');

      // prepare addons root-page
      $addonPage = null;
      if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission(rex::getUser()))
      {
        $addonPage = $page;
      }
      else if($perm == '' || rex::getUser()->hasPerm($perm) || rex::getUser()->isAdmin())
      {
        if ($title != '')
        {
          $addonPage = new rex_be_page($title, array('page' => $addonName));
          $addonPage->setHref($href);
        }
      }

      if($addonPage)
      {
        // adds be_page's
        foreach($addon->getProperty('pages', array()) as $s)
        {
          if (is_array($s))
          {
            if (!isset($s[2]) || rex::getUser()->hasPerm($s[2]) || rex::getUser()->isAdmin())
            {
              $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
              $subPage->setHref('index.php?page='.$addonName.'&subpage='.$s[0]);
              $addonPage->addSubPage($subPage);
            }
          } else if (rex_be_page_main::isValid($s))
          {
            $p = $s->getPage();
            $pages[$addonName.'_'.$p->getTitle()] = $s;
          } else if (rex_be_page::isValid($s) && $addonPage)
          {
            $addonPage->addSubPage($s);
          }
        }
      }

      // handle plugins
      $plugins = rex::isSafeMode() ? $addon->getSystemPlugins() : $addon->getAvailablePlugins();
      foreach($plugins as $pluginName => $plugin)
      {
        $page  = $plugin->getProperty('page', null);

        $title = $plugin->getProperty('name', '');
        $href  = $plugin->getProperty('link',  'index.php?page='. $addonName . '&subpage='. $pluginName);
        $perm  = $plugin->getProperty('perm', '');

        // prepare plugins root-page
        $pluginPage = null;
        if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission(rex::getUser()))
        {
          $pluginPage = $page;
        }
        else if ($perm == '' || rex::getUser()->hasPerm($perm) || rex::getUser()->isAdmin())
        {
          if($title != '')
          {
            $pluginPage = new rex_be_page($title, array('page' => $addonName, 'subpage' => $pluginName));
            $pluginPage->setHref($href);
          }
        }

        // add plugin-be_page's to addon
        foreach($plugin->getProperty('pages', array()) as $s)
        {
          if(is_array($s) && $addonPage)
          {
            if (!isset($s[2]) || rex::getUser()->hasPerm($s[2]) || rex::getUser()->isAdmin())
            {
              $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
              $subPage->setHref('index.php?page='.$addonName.'&subpage='.$s[0]);
              $addonPage->addSubPage($subPage);
            }
          }
          else if(rex_be_page_main::isValid($s))
          {
            $p = $s->getPage();
            $pages[$addonName.'_'.$pluginName.'_'.$p->getTitle()] = $s;
          }
          else if(rex_be_page::isValid($s) && $addonPage)
          {
            $addonPage->addSubPage($s);
          }
        }

        if($pluginPage)
        {
          if(rex_be_page_main::isValid($pluginPage))
          {
            if(!$pluginPage->getPage()->hasPath())
            {
              $pagePath = rex_path::plugin($addonName, $pluginName, 'pages/index.inc.php');
              $pluginPage->getPage()->setPath($pagePath);
            }
            $pages[$pluginName] = $pluginPage;
          }
          else
          {
            // "navigation" adds attributes to the plugin-root page
            $navProperties = $plugin->getProperty('navigation', array());
            // if there are some navigation attributes set, create a main page and apply attributes to it
            if(count($navProperties) > 0)
            {
              $mainPluginPage = new rex_be_page_main($addonName, $pluginPage);
              foreach($navProperties as $key => $value)
              {
                $mainPluginPage->_set($key, $value);
              }
              $pages[$addonName.'_'.$pluginName] = $mainPluginPage;
            }
            // if no navigation attributes can be found, we add the pluginPage as subPage of the addon
            else if($addonPage)
            {
              $addonPage->addSubPage($pluginPage);
            }
          }
        }
      }

      if(rex_be_page_main::isValid($addonPage))
      {
        // addonPage was defined as a main-page itself, so we only need to add it to REX
        $pages[$addonName] = $addonPage;
      }
      else
      {
        // wrap the be_page into a main_page
        $mainAddonPage = null;
        if ($addonPage)
        {
          $mainAddonPage = new rex_be_page_main('addons', $addonPage);

          // "navigation" adds attributes to the addon-root page
          foreach($addon->getProperty('navigation', array()) as $key => $value)
          {
            $mainAddonPage->_set($key, $value);
          }
          $pages[$addonName] = $mainAddonPage;
        }
      }
    }

    return $pages;
  }
}