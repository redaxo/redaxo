<?php

class rex_view
{
  static public function info($message, $cssClass = null, $sorround_tag = null)
  {
    if(!$cssClass) $cssClass = 'rex-info';
    if(!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClass, $sorround_tag);
  }

  static public function success($message, $cssClass = null, $sorround_tag = null)
  {
    if(!$cssClass) $cssClass = 'rex-success';
    if(!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClass, $sorround_tag);
  }

  static public function warning($message, $cssClass = null, $sorround_tag = null)
  {
    if(!$cssClass) $cssClass = 'rex-warning';
    if(!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClass, $sorround_tag);
  }

  static private function message($message, $cssClass, $sorround_tag)
  {
    $return = '';

    $return = '<div class="rex-section"><div class="rex-message"><'. $sorround_tag .' class="'. $cssClass .'">';

    if ($sorround_tag != 'p')
      $return .= '<p>';

    $return .= '<span>'. $message .'</span>';

    if ($sorround_tag != 'p')
      $return .= '</p>';

    $return .= '</'. $sorround_tag .'></div></div>';

    if ($sorround_tag != 'p')
      $message = '<p>'.$message.'</p>';
    /*
    $fragment = new rex_fragment();
    $fragment->setVar('class', $cssClass);
    $fragment->setVar('message', $content, false);
    $return = $fragment->parse('message');
    */
    return $return;
  }

  static public function infoBlock($message, $cssClass = null, $sorround_tag = null)
  {
    if(!$cssClass) $cssClass = 'rex-info-block';
    if(!$sorround_tag) $sorround_tag = 'div';
    return self::messageBlock($message, $cssClass, $sorround_tag);
  }

  static public function warningBlock($message, $cssClass = null, $sorround_tag = null)
  {
    if(!$cssClass) $cssClass = 'rex-warning-block';
    if(!$sorround_tag) $sorround_tag = 'div';
    return self::messageBlock($message, $cssClass, $sorround_tag);
  }

  static private function messageBlock($message, $cssClass, $sorround_tag)
  {
    return '<div class="rex-message-block">
              <'. $sorround_tag .' class="'. $cssClass .'">
                <div class="rex-message-content">
                  '. $message .'
                </div>
              </'. $sorround_tag .'>
            </div>';
  }

  static public function toolbar($content, $cssClass = null)
  {
    $return = '';
    $fragment = new rex_fragment();
    $fragment->setVar('class', $cssClass);
    $fragment->setVar('content', $content, false);
    $return = $fragment->parse('toolbar');

    return $return;
  }

  static public function contentBlock($content)
  {
    return '<div class="rex-content-block"><div class="rex-content-block-content">'. $content .'</div></div>';
  }

  /**
  * Ausgabe des Seitentitels
  *
  *
  * Beispiel für einen Seitentitel
  *
  * <code>
  * $subpages = array(
  *  array( ''      , 'Index'),
  *  array( 'lang'  , 'Sprachen'),
  *  array( 'groups', 'Gruppen')
  * );
  *
  * echo rex_view::title( 'Headline', $subpages)
  * </code>
  *
  *
  * Beispiel für einen Seitentitel mit Rechteprüfung
  *
  * <code>
  * $subpages = array(
  *  array( ''      , 'Index'   , 'index_perm'),
  *  array( 'lang'  , 'Sprachen', 'lang_perm'),
  *  array( 'groups', 'Gruppen' , 'group_perm')
  * );
  *
  * echo rex_view::title( 'Headline', $subpages)
  * </code>
  *
  *
  * Beispiel für einen Seitentitel eigenen Parametern
  *
  * <code>
  * $subpages = array(
  *  array( ''      , 'Index'   , '', array('a' => 'b')),
  *  array( 'lang'  , 'Sprachen', '', 'a=z&x=12'),
  *  array( 'groups', 'Gruppen' , '', array('clang' => rex_clang::getId()))
  * );
  *
  * echo rex_view::title( 'Headline', $subpages)
  * </code>
  */
  static public function title($head, $subtitle = '')
  {
    global $article_id, $category_id, $page;

    if(empty($subtitle))
    {
      $pages = rex::getProperty('pages');
      $subtitle = $pages[rex::getProperty('page')]->getPage()->getSubPages();
    }

    if(is_array($subtitle) && isset($subtitle[0]) && $subtitle[0] instanceof rex_be_page_container)
    {
      $nav = rex_be_navigation::factory();
      $nav->setHeadline('default', rex_i18n::msg('subnavigation', $head));
      foreach($subtitle as $pageObj)
      {
        $nav->addPage($pageObj);
      }
      $nav->setActiveElements();
      $subtitle = $nav->getNavigation();
    }
    else
    {
      // REDAXO <= 4.2 compat
      $subtitle = self::getSubtitle($subtitle);
    }


    $fragment = new rex_fragment();
    $fragment->setVar('category_id', $category_id, false);
    $fragment->setVar('article_id', $article_id, false);
    $fragment->setVar('page', $page, false);
    $fragment->setVar('title', rex_extension::registerPoint('PAGE_TITLE', $head, array('category_id' => $category_id, 'article_id' => $article_id, 'page' => $page)), false);
    $fragment->setVar('subtitle', $subtitle, false);
    $title = $fragment->parse('title');
    unset($fragment);
    return $title;
  }

  /**
   * Helper function, die den Subtitle generiert
   */
  static private function getSubtitle($subline)
  {
    if (empty($subline))
    {
      return  '';
    }

    $subtitle_str = $subline;
    $subtitle = $subline;
    $attr = '';
    $cur_subpage = rex_request('subpage', 'string');
    $cur_page    = rex_request('page', 'string');

    if (is_array($subline) && count($subline) > 0)
    {
      $subtitle = array();
      $numPages = count($subline);

      foreach ($subline as $subpage)
      {
        if (!is_array($subpage))
        {
          continue;
        }

        $link = $subpage[0];
        $label = $subpage[1];

        $perm = !empty($subpage[2]) ? $subpage[2] : '';
        $params = !empty($subpage[3]) ? rex_param_string($subpage[3]) : '';
        // Berechtigung prüfen
        if ($perm != '')
        {
          // Hat der User das Recht für die aktuelle Subpage?
          if (!rex::getUser()->isAdmin() && !rex::getUser()->hasPerm($perm))
          {
            // Wenn der User kein Recht hat, und diese Seite öffnen will -> Fehler
            if ($cur_subpage == $link)
            {
              exit ('You have no permission to this area!');
            }
            // Den Punkt aus der Navi entfernen
            else
            {
              continue;
            }
          }
        }

        // Falls im Link parameter enthalten sind, diese Abschneiden
        if (($pos = strpos($link, '&')) !== false)
        {
          $link = substr($link, 0, $pos);
        }

        $active = (empty ($cur_subpage) && $link == '') || (!empty ($cur_subpage) && $cur_subpage == $link);

        // restliche attribute direkt in den link-tag schreiben
        $add_class = '';
        if(!empty($subpage[4]) && is_array($subpage[4]))
        {
          foreach($subpage[4] as $attr_name => $attr_value)
          {
            if($active && $attr_name == 'class')
            {
              $add_class = ' '.$attr_value;
              break;
            }
            $attr .= ' '.$attr_name .'="'. $attr_value .'"';
          }
        }

        // Auf der aktiven Seite den Link nicht anzeigen
        if ($active)
        {
          // $format = '%s';
          // $subtitle[] = sprintf($format, $label);
          $format = '<a href="?page='. $cur_page .'&amp;subpage=%s%s"%s class="rex-active%s">%s</a>';
          $subtitle[] = sprintf($format, $link, $params, $attr, $add_class, $label);
        }
        elseif ($link == '')
        {
          $format = '<a href="?page='. $cur_page .'%s"%s>%s</a>';
          $subtitle[] = sprintf($format, $params, $attr, $label);
        }
        else
        {
          $format = '<a href="?page='. $cur_page .'&amp;subpage=%s%s"%s>%s</a>';
          $subtitle[] = sprintf($format, $link, $params, $attr, $label);
        }
      }


      if(!empty($subtitle))
      {
        $items = '';
        $i = 1;
        foreach($subtitle as $part)
        {
          if($i == 1)
          $items .= '<li class="rex-navi-first">'. $part .'</li>';
          else
          $items .= '<li>'. $part .'</li>';

          $i++;
        }
        //      <div class="rex-navi-page">
        $subtitle_str = '
        <div id="rex-navi-page">
        <ul class="rex-navi">
          '. $items .'
        </ul>
        </div>
        ';
      }
    }
    // \n aus Quellcode formatierungsgründen
    return $subtitle_str;
  }
}