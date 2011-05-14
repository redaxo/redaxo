<?php

class rex_debug_util {
  
  /**
   * Injects some content into the page-content of a backend page
   *
   * @param string $toInject the html-string to inject
   * @param string $pageOutput the source of the current page
   */
  static public function injectHtml($toInject, $pageOutput)
  {
    return str_replace('<div id="sidebar">', '<div>'. $toInject .'</div><div id="sidebar">', $pageOutput);
  }
}