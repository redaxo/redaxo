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
  
  public function addPage(/*rex_be_page_container*/ &$mainPage)
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
    global $REX,$I18N;
    $s = '<dl class="rex-navi">';
    if(is_array($this->pages))
    {
	    foreach($this->pages as $block => $blockPages)
	    {
        // PHP4 compat notation
	      $n = $this->_getNavigation($this->pages[$block], 0, $block);
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
  
  private function _getNavigation(&$blockPages, $level = 0, $block = '')
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
        // PHP4 compat notation
        $page =& $blockPages[$key]->getPage();
        
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
	        
	        $subpages =& $page->getSubPages();
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
          // PHP4 compat notation
	        $page =& $this->pages[$block][$mn]->getPage();
	        
	        // check main pages
	        $condition = $page->getActivateCondition();
	        if($this->checkActivateCondition($condition))
	        {
	          $page->addItemClass('rex-active');
	          
	          // check for subpages
  	        $subpages =& $page->getSubPages();
  	        foreach($subpages as $sn => $subpage)
  	        {
              // PHP4 compat notation
  	          $condition = $subpages[$sn]->getActivateCondition();
  	          if($this->checkActivateCondition($condition))
  	          {
  	            $subpages[$sn]->addItemClass('rex-active');
  	          }
  	        }
	        }
	      }
	    }
    }
  }
  
  private function checkActivateCondition($a)
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
    global $I18N;

    if (isset($this->headlines[$block]))
      return $this->headlines[$block];

    if ($block != 'default')
      return $I18N->msg('navigation_'.$block);
    
    return '';
  }
  
  static public function getSetupPage()
  {
    global $I18N;
      
    $page = new rex_be_page($I18N->msg('setup'), 'system');
    $page->setIsCorePage(true);
    return $page;
  }
  
  static public function getLoginPage()
  {
    $page = new rex_be_page('login', 'system');
    $page->setIsCorePage(true);
    $page->setHasNavigation(false);
    return $page;
  }
  
  static public function getLoggedInPages(/*rex_login_sql*/ $rexUser)
  {
    global $I18N;
    
    $pages = array();
    
    $profile = new rex_be_page($I18N->msg('profile'));
    $profile->setIsCorePage(true);
    $pages['profile'] = $profile;
    
    $credits = new rex_be_page($I18N->msg('credits'));
    $credits->setIsCorePage(true);
    $pages['credits'] = $credits;
    
    $mpool = new rex_be_page_popup($I18N->msg('mediapool'), 'openMediaPool(); return false;');
    $mpool->setIsCorePage(true);
    $mpool->setRequiredPermissions('hasMediaPerm');
    $pages['mediapool'] = new rex_be_page_main('system', $mpool); 
    
    $linkmap = new rex_be_page_popup($I18N->msg('linkmap'));
    $linkmap->setIsCorePage(true);
    $linkmap->setRequiredPermissions('hasStructurePerm');
    $pages['linkmap'] = $linkmap;
    
    $user = new rex_be_page($I18N->msg('user'), array('page'=>'user'));
    $user->setIsCorePage(true);
    $user->setRequiredPermissions('isAdmin');
    $pages['user'] = new rex_be_page_main('system', $user);
      
    $addon = new rex_be_page($I18N->msg('addon'), array('page'=>'addon'));
    $addon->setIsCorePage(true);
    $addon->setRequiredPermissions('isAdmin');
    $pages['addon'] = new rex_be_page_main('system', $addon);

    $settings = new rex_be_page($I18N->msg('main_preferences'), array('page'=>'specials', 'subpage' => ''));
    $settings->setIsCorePage(true);
    $settings->setRequiredPermissions('isAdmin');
    $settings->setHref('index.php?page=specials&subpage=');
    
    $languages = new rex_be_page($I18N->msg('languages'), array('page'=>'specials', 'subpage' => 'lang'));
    $languages->setIsCorePage(true);
    $languages->setRequiredPermissions('isAdmin');
    $languages->setHref('index.php?page=specials&subpage=lang');
    
    $syslog = new rex_be_page($I18N->msg('languages'), array('page'=>'specials', 'subpage' => 'log'));
    $syslog->setIsCorePage(true);
    $syslog->setRequiredPermissions('isAdmin');
    $syslog->setHref('index.php?page=specials&subpage=log');
    
    $mainSpecials = new rex_be_page($I18N->msg('specials'), array('page'=>'specials'));
    $mainSpecials->setIsCorePage(true);
    $mainSpecials->setRequiredPermissions('isAdmin');
    $mainSpecials->addSubPage($settings);
    $mainSpecials->addSubPage($languages);
    $mainSpecials->addSubPage($syslog);
    $pages['specials'] = new rex_be_page_main('system', $mainSpecials);
    
    return $pages;    
  }
}
