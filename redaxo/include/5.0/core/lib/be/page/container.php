<?php

/**
 * Page Container interface for classes which may hold a be page
 * @author staabm
 */
interface rex_be_page_container
{
  /**
   * Returns the page which wrapped in this container
   */
  function getPage();
}
