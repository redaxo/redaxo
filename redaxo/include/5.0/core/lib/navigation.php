<?php

/**
 * Klasse zum Erstellen von Navigationen, v0.1
 *
 * @package redaxo4
 * @version svn:$Id$
 */

/*
 * Beispiel:
 *
 * UL, LI Navigation von der Rootebene aus,
 * 2 Ebenen durchgehen, Alle unternavis offen
 * und offline categorien nicht beachten
 *
 * Navigation:
 * 
 * $nav = rex_navigation::factory();
 * $nav->setClasses(array('lev1', 'lev2', 'lev3'));
 * $nav->setLinkClasses(array('alev1', 'alev2', 'alev3'));
 * echo $nav->get(0,2,TRUE,TRUE);
 *
 * Sitemap:
 *
 * $nav = rex_navigation::factory();
 * $nav->show(0,-1,TRUE,TRUE);
 * 
 * Breadcrump:
 * 
 * $nav = rex_navigation::factory();
 * $nav->showBreadcrumb(true);
 */

class rex_navigation
{
	var $depth; // Wieviele Ebene tief, ab der Startebene
	var $open; // alles aufgeklappt, z.b. Sitemap
	var $ignore_offlines;
	var $path = array();
	var $classes = array();

	var $current_article_id = -1; // Aktueller Artikel
	var $current_category_id = -1; // Aktuelle Katgorie

	/*private*/ function rex_navigation()
	{
	  // nichts zu tun
	}

  /*public*/ function factory()
  {
    static $class = null;

    if(!$class)
    {
      // ----- EXTENSION POINT
      $class = rex_register_extension_point('REX_NAVI_CLASSNAME', 'rex_navigation');
    }

    return new $class();
  }
  
  /**
   * Generiert eine Navigation
   * 
   * @param $category_id Id der Wurzelkategorie
   * @param $depth Anzahl der Ebenen die angezeigt werden sollen
   * @param $open True, wenn nur Elemente der aktiven Kategorie angezeigt werden sollen, sonst FALSE
   * @param $ignore_offlines FALSE, wenn offline Elemente angezeigt werden, sonst TRUE
   */
	/*public*/ function get($category_id = 0,$depth = 3,$open = FALSE, $ignore_offlines = FALSE)
	{
    if(!$this->_setActivePath()) return FALSE;
    
	  $this->depth = $depth;
    $this->open = $open;
    $this->ignore_offlines = $ignore_offlines;
	  
		return $this->_getNavigation($category_id,$this->ignore_offlines);
	}

  /**
   * @see get()
   */
	/*public*/ function show($category_id = 0,$depth = 3,$open = FALSE, $ignore_offlines = FALSE)
	{
		echo $this->get($category_id, $depth, $open, $ignore_offlines);
	}
	
  /**
   * Generiert eine Breadcrumb-Navigation
   * 
   * @param $startPageLabel Label der Startseite, falls FALSE keine Start-Page anzeigen
   * @param $includeCurrent True wenn der aktuelle Artikel enthalten sein soll, sonst FALSE
   * @param $category_id Id der Wurzelkategorie
   */
	/*public*/ function getBreadcrumb($startPageLabel, $includeCurrent = FALSE, $category_id = 0)
	{
	  if(!$this->_setActivePath()) return FALSE;
	  
	  global $REX;
    
	  $path = $this->path;
            
    $i = 1;
    $lis = '';
    
    if($startPageLabel)
    {
      $lis .= '<li class="rex-lvl'. $i .'"><a href="'. rex_getUrl($REX['START_ARTICLE_ID']) .'">'. htmlspecialchars($startPageLabel) .'</a></li>';
      $i++;

      // StartArticle nicht doppelt anzeigen
      if(isset($path[0]) && $path[0] == $REX['START_ARTICLE_ID'])
      {
        unset($path[0]);
      }
    }
    
    foreach($path as $pathItem)
    {
      $cat = OOCategory::getCategoryById($pathItem);
      $lis .= '<li class="rex-lvl'. $i .'"><a href="'. $cat->getUrl() .'">'. htmlspecialchars($cat->getName()) .'</a></li>';
      $i++;
    }
    
    if($includeCurrent)
    {
      if($art = OOArticle::getArticleById($this->current_article_id))
        if(!$art->isStartpage())
        {
          $lis .= '<li class="rex-lvl'. $i .'">'. htmlspecialchars($art->getName()) .'</li>';
        }else
        {
        	$cat = OOCategory::getCategoryById($this->current_article_id);
          $lis .= '<li class="rex-lvl'. $i .'">'. htmlspecialchars($cat->getName()) .'</li>';
        }
    }
    
    return '<ul class="rex-breadcrumb">'. $lis .'</ul>';
	}
	
	/**
	 * @see getBreadcrumb()
	 */
  /*public*/ function showBreadcrumb($includeCurrent = FALSE, $category_id = 0)
  {
    echo $this->getBreadcrumb($includeCurrent, $category_id);
  }
  
	/*public*/ function setClasses($classes)
	{
	  $this->classes = $classes;
	}

	/*public*/ function setLinkClasses($classes)
	{
	  $this->linkclasses = $classes;
	}

	/*private*/ function _setActivePath()
	{
		global $REX;

		$article_id = $REX["ARTICLE_ID"];
		if($OOArt = OOArticle::getArticleById($article_id))
		{
		  $path = trim($OOArt->getValue("path"), '|');
		  
		  $this->path = array();
		  if($path != "")
			 $this->path = explode("|",$path);
			 
      $this->current_article_id = $article_id;
			$this->current_category_id = $OOArt->getCategoryId();
			return TRUE;
		}
		
		return FALSE;
	}

	/*protected*/ function _getNavigation($category_id,$ignore_offlines = TRUE)
	{
	  static $depth = 0;
	  
    if($category_id < 1)
	  	$nav_obj = OOCategory::getRootCategories($ignore_offlines);
		else
	  	$nav_obj = OOCategory::getChildrenById($category_id, $ignore_offlines);

  	$return = "";

		if(count($nav_obj)>0)
		  $return .= '<ul class="rex-navi'. ($depth+1) .'">';

		foreach($nav_obj as $nav)
		{
		  $liClass = '';
		  $linkClass = '';
		  
		  // classes abhaengig vom pfad
			if($nav->getId() == $this->current_category_id)
			{
			  $liClass .= ' rex-current';
			  $linkClass .= ' rex-current';
			}
			elseif (in_array($nav->getId(),$this->path))
			{
        $liClass .= ' rex-active';
			  $linkClass .= ' rex-active';
			}
			else
			{
        $liClass .= ' rex-normal';
			}
			
      // classes abhaengig vom level
      if(isset($this->classes[$depth]))
        $liClass .= ' '. $this->classes[$depth];

      if(isset($this->linkclasses[$depth]))
        $linkClass .= ' '. $this->linkclasses[$depth];



			$linkClass = $linkClass == '' ? '' : ' class="'. ltrim($linkClass) .'"';
			  
      $return .= '<li class="rex-article-'. $nav->getId() . $liClass .'">';
			$return .= '<a'. $linkClass .' href="'.$nav->getUrl().'">'.htmlspecialchars($nav->getName()).'</a>';

			$depth++;
			if(($this->open || 
			    $nav->getId() == $this->current_category_id || 
			    in_array($nav->getId(),$this->path))
         && ($this->depth > $depth || $this->depth < 0))
			{
				$return .= $this->_getNavigation($nav->getId(),$ignore_offlines);
			}
			$depth--;

			$return .= '</li>';
		}

		if(count($nav_obj)>0)
	  	$return .= '</ul>';

		return $return;
	}
}

class rex_be_navigation
{
  var $headlines = array();
  var $pages;
  
  /*public*/ function factory()
  {
    static $class = null;

    if(!$class)
    {
      // ----- EXTENSION POINT
      $class = rex_register_extension_point('REX_BE_NAVI_CLASSNAME', 'rex_be_navigation');
    }

    return new $class();
  }
  
  /*public*/ function addPage(/*rex_be_page_container*/ &$mainPage)
  {
    $blockName = 'default';
    if(rex_be_main_page::isValid($mainPage))
    {
      $blockName = $mainPage->getBlock();
    }
    
    if(!isset($this->pages[$blockName]))
    {
      $this->pages[$blockName] = array();
    }
      
    $this->pages[$blockName][] = $mainPage;
  }
  
  /*public*/ function getNavigation()
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
  
  /*private*/ function _getNavigation(&$blockPages, $level = 0, $block = '')
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
  
  /*public*/ function setActiveElements()
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
  
  /*private*/ function checkActivateCondition($a)
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
  
  /*public*/ function setHeadline($block, $headline)
  {
    $this->headlines[$block] = $headline;
  }
  
  /*public*/ function getHeadline($block)
  {
    global $I18N;

    if (isset($this->headlines[$block]))
      return $this->headlines[$block];

    if ($block != 'default')
      return $I18N->msg('navigation_'.$block);
    
    return '';
  }
  
  /*public static*/ function getSetupPage()
  {
    global $I18N;
      
    $page = new rex_be_page($I18N->msg('setup'), 'system');
    $page->setIsCorePage(true);
    return $page;
  }
  
  /*public static*/ function getLoginPage()
  {
    $page = new rex_be_page('login', 'system');
    $page->setIsCorePage(true);
    $page->setHasNavigation(false);
    return $page;
  }
  
  /*public static*/ function getLoggedInPages(/*rex_login_sql*/ $rexUser)
  {
    global $I18N;
    
    $pages = array();
    
    $profile = new rex_be_page($I18N->msg('profile'));
    $profile->setIsCorePage(true);
    $pages['profile'] = $profile;
    
    $credits = new rex_be_page($I18N->msg('credits'));
    $credits->setIsCorePage(true);
    $pages['credits'] = $credits;
    
    $structure = new rex_be_page($I18N->msg('structure'), array('page' => 'structure'));
    $structure->setIsCorePage(true);
    $structure->setRequiredPermissions('hasStructurePerm');
    $pages['structure'] = new rex_be_main_page('system', $structure); 
    
    $mpool = new rex_be_popup_page($I18N->msg('mediapool'), 'openMediaPool(); return false;');
    $mpool->setIsCorePage(true);
    $mpool->setRequiredPermissions('hasMediaPerm');
    $pages['mediapool'] = new rex_be_main_page('system', $mpool); 
    
    $linkmap = new rex_be_popup_page($I18N->msg('linkmap'));
    $linkmap->setIsCorePage(true);
    $linkmap->setRequiredPermissions('hasStructurePerm');
    $pages['linkmap'] = $linkmap;
    
    $content = new rex_be_page($I18N->msg('content'));
    $content->setIsCorePage(true);
    $content->setRequiredPermissions('hasStructurePerm');
    $pages['content'] = $content;
      
    $template = new rex_be_page($I18N->msg('template'), array('page'=>'template'));
    $template->setIsCorePage(true);
    $template->setRequiredPermissions('isAdmin');
    $pages['template'] = new rex_be_main_page('system', $template);
    
    $modules = new rex_be_page($I18N->msg('modules'), array('page'=>'module', 'subpage' => ''));
    $modules->setIsCorePage(true);
    $modules->setRequiredPermissions('isAdmin');
    $modules->setHref('index.php?page=module&subpage=');
    
    $actions = new rex_be_page($I18N->msg('actions'), array('page'=>'module', 'subpage' => 'actions'));
    $actions->setIsCorePage(true);
    $actions->setRequiredPermissions('isAdmin');
    $actions->setHref('index.php?page=module&subpage=actions');
    
    $mainModules = new rex_be_page($I18N->msg('modules'), array('page'=>'module'));
    $mainModules->setIsCorePage(true);
    $mainModules->setRequiredPermissions('isAdmin');
    $mainModules->addSubPage($modules);
    $mainModules->addSubPage($actions);
    $pages['module'] = new rex_be_main_page('system', $mainModules);
    
    $user = new rex_be_page($I18N->msg('user'), array('page'=>'user'));
    $user->setIsCorePage(true);
    $user->setRequiredPermissions('isAdmin');
    $pages['user'] = new rex_be_main_page('system', $user);
      
    $addon = new rex_be_page($I18N->msg('addon'), array('page'=>'addon'));
    $addon->setIsCorePage(true);
    $addon->setRequiredPermissions('isAdmin');
    $pages['addon'] = new rex_be_main_page('system', $addon);

    $settings = new rex_be_page($I18N->msg('main_preferences'), array('page'=>'specials', 'subpage' => ''));
    $settings->setIsCorePage(true);
    $settings->setRequiredPermissions('isAdmin');
    $settings->setHref('index.php?page=specials&subpage=');
    
    $languages = new rex_be_page($I18N->msg('languages'), array('page'=>'specials', 'subpage' => 'lang'));
    $languages->setIsCorePage(true);
    $languages->setRequiredPermissions('isAdmin');
    $languages->setHref('index.php?page=specials&subpage=lang');
    
    $mainSpecials = new rex_be_page($I18N->msg('specials'), array('page'=>'specials'));
    $mainSpecials->setIsCorePage(true);
    $mainSpecials->setRequiredPermissions('isAdmin');
    $mainSpecials->addSubPage($settings);
    $mainSpecials->addSubPage($languages);
    $pages['specials'] = new rex_be_main_page('system', $mainSpecials);
    
    return $pages;    
  }
}

class rex_be_page_container
{
  function getPage()
  {
    trigger_error('this method has to be overriden by subclass!', E_USER_ERROR);
  }
  
  /*
   * Static Method: Returns True when the given be_main_page is valid
   */
  /*public static*/ function isValid($be_page_container)
  {
    return is_object($be_page_container) && is_a($be_page_container, 'rex_be_page_container');
  }
}

class rex_be_page extends rex_be_page_container
{
  var $title;
  
  var $href;
  var $linkAttr;
  var $itemAttr;
  
  var $subPages;
  
  var $isCorePage;
  var $hasNavigation;
  var $activateCondition;
  var $requiredPermissions;
  var $path;
  
  function rex_be_page($title, $activateCondition = array(), $hidden = FALSE)
  {
    $this->title = $title;
    $this->subPages = array();
    $this->itemAttr = array();
    $this->linkAttr = array();
    
    $this->isCorePage = false;
    $this->hasNavigation = true;
    $this->activateCondition = $activateCondition;
    $this->requiredPermissions = array();
    $this->hidden = $hidden;
  }
  
  function &getPage()
  {
    return $this;
  }
  
  function getItemAttr($name, $default = '')
  {
    // return all attributes if null is passed as name
    if($name === null)
    {
      return $this->itemAttr;
    }
    
    return isset($this->itemAttr[$name]) ? $this->itemAttr[$name] : $default;
  }
  
  function setItemAttr($name, $value)
  {
    $this->itemAttr[$name] = $value;
  }
  
  function addItemClass($class)
  {
    $this->setItemAttr('class', ltrim($this->getItemAttr('class').' '. $class));
  }
  
  function getLinkAttr($name, $default = '')
  {
    // return all attributes if null is passed as name
    if($name === null)
    {
      return $this->linkAttr;
    }
    
    return isset($this->linkAttr[$name]) ? $this->linkAttr[$name] : $default;
  }
  
  function setLinkAttr($name, $value)
  {
    $this->linkAttr[$name] = $value;
  }
  
  function addLinkClass($class)
  {
    $this->setLinkAttr('class', ltrim($this->getLinkAttr('class').' '. $class));
  }
  
  function setHref($href)
  {
    $this->href = $href;
  }
  
  function getHref()
  {
    return $this->href;
  }

  function setHidden($hidden = TRUE)
  {
    $this->hidden = $hidden;
  }
  
  function getHidden()
  {
    return $this->hidden;
  }
  
  function setIsCorePage($isCorePage)
  {
    $this->isCorePage = $isCorePage;
  }
  
  function setHasNavigation($hasNavigation)
  {
    $this->hasNavigation = $hasNavigation;
  }
  
  function addSubPage(/*rex_be_page*/ $subpage)
  {
    $this->subPages[] = $subpage;
  }
  
  function &getSubPages()
  {
    return $this->subPages;
  }
  
  function getTitle()
  {
    return $this->title;
  }
  
  function setActivateCondition($activateCondition)
  {
    $this->activateCondition = $activateCondition;
  }
  
  function getActivateCondition()
  {
    return $this->activateCondition;
  }
  
  function isCorePage()
  {
    return $this->isCorePage;  
  }
  
  function hasNavigation()
  {
    return $this->hasNavigation;
  }
  
  function setRequiredPermissions($perm)
  {
    $this->requiredPermissions = (array) $perm;
  }
  
  function getRequiredPermissions()
  {
    return $this->requiredPermissions;
  }
  
  function checkPermission(/*rex_login_sql*/ $rexUser)
  {
    foreach($this->requiredPermissions as $perm)
    {
      if(!$rexUser->hasPerm($perm))
      {
        return false;
      }
    }
    return true;
  }
  
  function setPath($path)
  {
    $this->path = $path;
  }
  
  function hasPath()
  {
    return !empty($this->path);
  }
  
  function getPath()
  {
    return $this->path;
  }
  
  /*
   * Static Method: Returns True when the given be_page is valid
   */
  /*public static*/ function isValid($be_page)
  {
    return is_object($be_page) && is_a($be_page, 'rex_be_page');
  }
}

class rex_be_main_page extends rex_be_page_container
{
  var $block;
  var $page;
  
  function rex_be_main_page($block, /*rex_be_page*/ $page)
  {
    $this->block = $block;
    $this->page = $page;
  }
  
  function setBlock($block)
  {
    $this->block = $block;
  }
  
  function getBlock()
  {
    return $this->block;
  }
  
  function &getPage()
  {
    return $this->page;
  }
  
  function _set($key, $value)
  {
    if(!is_string($key))
      return;
      
    // check current object for a possible setter
    $setter = array(&$this, 'set'. ucfirst($key));
    if(is_callable($setter))
    {
      call_user_func($setter, $value);
    }
    else
    {
      // no setter found, delegate to page object
      $setter = array(&$this->page, 'set'. ucfirst($key));
      if(is_callable($setter))
      {
        call_user_func($setter, $value);
      }
    }
  }
  
  /*
   * Static Method: Returns True when the given be_main_page is valid
   */
  /*public static*/ function isValid($be_main_page)
  {
    return is_object($be_main_page) && is_a($be_main_page, 'rex_be_main_page');
  }
}

class rex_be_popup_page extends rex_be_page
{
  function rex_be_popup_page($title, $onclick = '', $activateCondition = array())
  {
    parent::rex_be_page($title, $activateCondition);
    
    $this->setHasNavigation(false);
    $this->onclick = $onclick;
    $this->addItemClass('rex-popup');
    $this->addLinkClass('rex-popup');
    $this->setLinkAttr('onclick', $onclick);
  }
}