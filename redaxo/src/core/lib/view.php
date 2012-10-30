<?php

class rex_view
{
  static public function info($message, $cssClass = '', $sorround_tag = null)
  {
    $cssClassMessage = 'rex-info';
    if ($cssClass != '')
      $cssClassMessage .= ' ' . $cssClass;

    if (!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClassMessage, $sorround_tag);
  }

  static public function success($message, $cssClass = '', $sorround_tag = null)
  {
    $cssClassMessage = 'rex-success';
    if ($cssClass != '')
      $cssClassMessage .= ' ' . $cssClass;

    if (!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClassMessage, $sorround_tag);
  }

  static public function warning($message, $cssClass = '', $sorround_tag = null)
  {
    $cssClassMessage = 'rex-warning';
    if ($cssClass != '')
      $cssClassMessage .= ' ' . $cssClass;

    if (!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClassMessage, $sorround_tag);
  }

  static public function error($message, $cssClass = '', $sorround_tag = null)
  {
    $cssClassMessage = 'rex-error';
    if ($cssClass != '')
      $cssClassMessage .= ' ' . $cssClass;

    if (!$sorround_tag) $sorround_tag = 'div';
    return self::message($message, $cssClassMessage, $sorround_tag);
  }

  static private function message($message, $cssClass, $sorround_tag)
  {
    $return = '';

    $cssClassMessage = 'rex-message';
    if ($cssClass != '')
      $cssClassMessage .= ' ' . $cssClass;

    $return = '<' . $sorround_tag . ' class="' . $cssClassMessage . '">' . $message . '</' . $sorround_tag . '>';

    /*
    $fragment = new rex_fragment();
    $fragment->setVar('class', $cssClass);
    $fragment->setVar('message', $content, false);
    $return = $fragment->parse('message.tpl');
    */
    return $return;
  }

  static public function toolbar($content, $cssClass = null)
  {
    $return = '';
    $fragment = new rex_fragment();
    $fragment->setVar('class', $cssClass);
    $fragment->setVar('content', $content, false);
    $return = $fragment->parse('toolbar.tpl');

    return $return;
  }

  static public function contentBlock($content_1 = '', $content_2 = '', $type = '')
  {
    $return = '';

    $class = '';
    switch ($type) {
      case 'plain':
        $class = ' rex-content-plain';
        break;
      case 'block':
        $class = ' rex-content-block';
        break;
      default:
        $class = ' rex-content-default';
        break;
    }

    $return .= '<section class="rex-content' . $class . '">';
    if ($content_2 != '') {
      $return .= '
        <div class="rex-grid2col">
          <div class="rex-column rex-first">' . $content_1 . '</div>
          <div class="rex-column rex-last">' . $content_2 . '</div>
        </div>';

    } else {
      $return .= $content_1;
    }
    $return .= '</section>';

    return $return;
  }



  /**
   * Ausgabe des Seitentitels
   */
  static public function title($head, $subtitle = '')
  {
    global $article_id, $category_id, $page;

    if (!is_string($subtitle) && (!is_array($subtitle) || count($subtitle) > 0 && !reset($subtitle) instanceof rex_be_page_container)) {
      throw new rex_exception('Expecting $subtitle to be a string or an array of rex_be_page_container!');
    }

    if (empty($subtitle)) {
      $subtitle = rex_be_controller::getPageObject(rex_be_controller::getCurrentPagePart(1))->getPage()->getSubPages();
    }

    if (is_array($subtitle) && count($subtitle) && reset($subtitle) instanceof rex_be_page_container) {
      $nav = rex_be_navigation::factory();
      $nav->setHeadline('default', rex_i18n::msg('subnavigation', $head));
      foreach ($subtitle as $pageObj) {
        $nav->addPage($pageObj);
      }
      $nav->setActiveElements();
      $blocks = $nav->getNavigation();

      $fragment = new rex_fragment();
      $fragment->setVar('type', 'tab', false);
      $fragment->setVar('blocks', $blocks, false);
      $subtitle = $fragment->parse('navigation.tpl');

    } elseif (!is_string($subtitle)) {
      $subtitle = '';
    }

    $title = rex_extension::registerPoint('PAGE_TITLE', $head, array('category_id' => $category_id, 'article_id' => $article_id, 'page' => $page));

    $return = '<h1>' . $title . '</h1>' . $subtitle;

    echo rex_extension::registerPoint('PAGE_TITLE_SHOWN', '',
      array(
        'category_id' => $category_id,
        'article_id' => $article_id,
        'page' => $page
      )
    );

    return $return;
  }
}
