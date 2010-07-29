<?php

class rex_be_page_container
{
  function getPage()
  {
    trigger_error('this method has to be overriden by subclass!', E_USER_ERROR);
  }
  
  /*
   * Static Method: Returns True when the given be_main_page is valid
   */
  static public function isValid($be_page_container)
  {
    return is_object($be_page_container) && is_a($be_page_container, 'rex_be_page_container');
  }
}
