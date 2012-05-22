<?php

class rex_be_navigation extends rex_factory_base
{
  private
  $headlines = array(),
  $pages;

  static public function factory()
  {
    $class = self::getFactoryClass();
    return new $class();
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

    $return = array();
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
        if(count($n)>0)
        {
          $fragment = new rex_fragment();
          $fragment->setVar('navigation', $n, false);

          $return[] = array(
            'navigation' => $n,
            'headline' => array("title"=>$this->getHeadline($block))
          );
        }
      }
    }
    return $return;

  }

  private function _getNavigation(array $blockPages, $level = 0, $block = '')
  {

    $navigation = array();

    $level++;
    $id = '';
    if($block != '')
    {
      $id = ' id="rex-navi-'. $block .'"';
    }
    $class = ' class="rex-navi-level-'. $level .'"';

    $echo = '';
    $first = TRUE;
    foreach($blockPages as $key => $pageContainer)
    {
      $page = $pageContainer->getPage();

      if(!$page->getHidden() && $page->checkPermission(rex::getUser()))
      {
        $n = array();
        $n["linkClasses"] = array();
        $n["itemClasses"] = array();
        $n["linkAttr"] = array();
        $n["itemAttr"] = array();

        $n["itemClasses"][] = $page->getItemAttr('class');
        $n["linkClasses"][] = $page->getItemAttr('class');

        $itemAttr = '';
        foreach($page->getItemAttr(null) as $name => $value)
        {
          $n["itemAttr"][$name] = trim($value);
        }

        $linkAttr = '';
        foreach($page->getLinkAttr(null) as $name => $value)
        {
          $n["linkAttr"][$name] = trim($value);
        }

        $n["href"] = str_replace('&', '&amp;', $page->getHref());
        $n["title"] = $page->getTitle();

        $subpages = $page->getSubPages();
        if(is_array($subpages) && count($subpages) > 0)
        {
          $n["children"] = $this->_getNavigation($subpages, $level);
        }

        $navigation[] = $n;
      }
    }

    if(count($navigation)>0)
    {
      return $navigation; // $echo = '<ul'. $id . $class .'>'.$echo.'</ul>';
    }

    return array();
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
          if($page->isActive())
          {
            $page->addItemClass('rex-active');

            // check for subpages
            $subpages = $page->getSubPages();
            foreach($subpages as $sn => $subpage)
            {
              if($subpage->isActive())
              {
                $subpage->addItemClass('rex-active');
              }
            }
          }
        }
      }
    }
  }

  public function setHeadline($block, $headline)
  {
    $this->headlines[$block] = $headline;
  }

  public function getHeadline($block)
  {
    if (isset($this->headlines[$block]))
    return $this->headlines[$block];

    if ($block != 'default')
    return rex_i18n::msg('navigation_'.$block);

    return '';
  }
}
