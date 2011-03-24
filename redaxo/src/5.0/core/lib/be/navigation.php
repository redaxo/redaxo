<?php

class rex_be_navigation
{
  private static
    $class;
  private
    $headlines = array(),
    $pages;

  static public function factory()
  {
    if(!self::$class)
    {
      // ----- EXTENSION POINT
      self::$class = rex_register_extension_point('REX_BE_NAVI_CLASSNAME', 'rex_be_navigation');
    }

    return new self::$class();
  }

  public function addPage(rex_be_page_container $mainPage)
  {
    $blockName = 'default';
    if(rex_be_page_main::isValid($mainPage))
    {
      $blockName = $mainPage->getBlock();
    }

    if(!isset($this->pages[$blockName]))
    {
      $this->pages[$blockName] = array();
    }

    $this->pages[$blockName][] = $mainPage;
  }

  public function getNavigation()
  {
    global $REX;
    $s = '<dl class="rex-navi">';
    if(is_array($this->pages))
    {
	    foreach($this->pages as $block => $blockPages)
	    {
	      if(is_array($blockPages) && count($blockPages) > 0 && rex_be_page_main::isValid($blockPages[0]))
	      {
	        uasort($blockPages,
  	        function($a, $b)
  	        {
  	          $a_prio = (int) $a->getPrio();
  	          $b_prio = (int) $b->getPrio();
  	          if($a_prio == $b_prio || ($a_prio <= 0 && $b_prio <= 0))
  	            return strcmp($a->getPage()->getTitle(), $b->getPage()->getTitle());

  	          if($a_prio <= 0)
  	            return 1;

  	          if($b_prio <= 0)
  	            return -1;

  	          return $a_prio > $b_prio ? 1 : -1;
  	        }
	        );
        }

	      $n = $this->_getNavigation($blockPages, 0, $block);
     	  if($n != "")
        {
	        $headline = $this->getHeadline($block);
	        $s .= '<dt>'. $headline .'</dt><dd>';
	        $s .= $n;
	        $s .= '</dd>' . "\n";
        }
	    }
    }
    $s .= '</dl>';
    return $s;

  }

  private function _getNavigation(array $blockPages, $level = 0, $block = '')
  {
      global $REX;

      $level++;
      $id = '';
      if($block != '')
        $id = ' id="rex-navi-'. $block .'"';
      $class = ' class="rex-navi-level-'. $level .'"';

      $echo = '';
      $first = TRUE;
      foreach($blockPages as $key => $pageContainer)
      {
        $page = $pageContainer->getPage();

        if(!$page->getHidden() && $page->checkPermission($REX['USER']))
        {
          if($first)
          {
            $first = FALSE;
            $page->addItemClass('rex-navi-first');
          }
          $page->addLinkClass($page->getItemAttr('class'));

          $itemAttr = '';
          foreach($page->getItemAttr(null) as $name => $value)
          {
            $itemAttr .= $name .'="'. trim($value) .'" ';
          }

          $linkAttr = '';
          foreach($page->getLinkAttr(null) as $name => $value)
          {
            $linkAttr .= $name .'="'. trim($value) .'" ';
          }

          $href = str_replace('&', '&amp;', $page->getHref());

          $echo .= '<li '. $itemAttr .'><a '. $linkAttr . ' href="'. $href .'">'. $page->getTitle() .'</a>';

          $subpages = $page->getSubPages();
          if(is_array($subpages) && count($subpages) > 0)
          {
            $echo .= $this->_getNavigation($subpages, $level);
          }
          $echo .= '</li>';
        }
      }

      if($echo != "")
      {
        $echo = '<ul'. $id . $class .'>'.$echo.'</ul>';
      }

      return $echo;
  }

  public function setActiveElements()
  {
    if(is_array($this->pages))
    {
      foreach($this->pages as $block => $blockPages)
      {
        foreach($blockPages as $mn => $pageContainer)
        {
          $page = $pageContainer->getPage();

          // check main pages
          $condition = $page->getActivateCondition();
          if($this->checkActivateCondition($condition))
          {
            $page->addItemClass('rex-active');

            // check for subpages
  	        $subpages = $page->getSubPages();
  	        foreach($subpages as $sn => $subpage)
  	        {
  	          $condition = $subpage->getActivateCondition();
  	          if($this->checkActivateCondition($condition))
  	          {
  	            $subpage->addItemClass('rex-active');
  	          }
  	        }
          }
        }
      }
    }
  }

  private function checkActivateCondition(array $a)
  {
    if(empty($a))
    {
      return false;
    }
    foreach($a as $k => $v)
    {
      $v = (array)  $v;
      if(!in_array(rex_request($k), $v))
      {
        return FALSE;
      }
    }
    return TRUE;
  }

  public function setHeadline($block, $headline)
  {
    $this->headlines[$block] = $headline;
  }

  public function getHeadline($block)
  {
    global $REX;

    if (isset($this->headlines[$block]))
      return $this->headlines[$block];

    if ($block != 'default')
      return rex_i18n::msg('navigation_'.$block);

    return '';
  }

  static public function getSetupPage()
  {
    global $REX;

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
    global $REX;

    $pages = array();

    $profile = new rex_be_page(rex_i18n::msg('profile'));
    $profile->setIsCorePage(true);
    $pages['profile'] = $profile;

    $credits = new rex_be_page(rex_i18n::msg('credits'));
    $credits->setIsCorePage(true);
    $pages['credits'] = $credits;

    $addon = new rex_be_page(rex_i18n::msg('addon'), array('page'=>'addon'));
    $addon->setIsCorePage(true);
    $addon->setRequiredPermissions('isAdmin');
    $pages['addon'] = new rex_be_page_main('system', $addon);
    $pages['addon']->setPrio(60);

    $settings = new rex_be_page(rex_i18n::msg('main_preferences'), array('page'=>'specials', 'subpage' => ''));
    $settings->setIsCorePage(true);
    $settings->setRequiredPermissions('isAdmin');
    $settings->setHref('index.php?page=specials&subpage=');

    $languages = new rex_be_page(rex_i18n::msg('languages'), array('page'=>'specials', 'subpage' => 'lang'));
    $languages->setIsCorePage(true);
    $languages->setRequiredPermissions('isAdmin');
    $languages->setHref('index.php?page=specials&subpage=lang');

    $syslog = new rex_be_page(rex_i18n::msg('syslog'), array('page'=>'specials', 'subpage' => 'log'));
    $syslog->setIsCorePage(true);
    $syslog->setRequiredPermissions('isAdmin');
    $syslog->setHref('index.php?page=specials&subpage=log');

    $mainSpecials = new rex_be_page(rex_i18n::msg('specials'), array('page'=>'specials'));
    $mainSpecials->setIsCorePage(true);
    $mainSpecials->setRequiredPermissions('isAdmin');
    $mainSpecials->addSubPage($settings);
    $mainSpecials->addSubPage($languages);
    $mainSpecials->addSubPage($syslog);
    $pages['specials'] = new rex_be_page_main('system', $mainSpecials);
    $pages['specials']->setPrio(70);

    return $pages;
  }
}
