<?php

class rex_debug_util {
  
  static public function injectHtml($toInject, $pageOutput)
  {
    return str_replace('<div id="sidebar">', '<div>'. $toInject .'</div><div id="sidebar">', $pageOutput);
  }
}