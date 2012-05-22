<?php

/**
 * The rex_pager-class implements all the logic
 * which is necessary to implement some sort of pagination.
 *
 * @author staabm
 */
class rex_pager
{
  private $rowCount;
  private $rowsPerPage;
  private $cursorName;

  /**
   * Constructs a rex_pager
   *
   * @param int $rowCount The number of all rows to paginate
   * @param int $rowsPerPage The number of rows which should be displayed on one page
   * @param string $cursorName The name of the parameter used for pagination
   */
  public function __construct($rowCount, $rowsPerPage = 30, $cursorName = 'start')
  {
    $this->rowCount = $rowCount;
    $this->rowsPerPage = $rowsPerPage;
    $this->cursorName = $cursorName;
  }

  /**
   * Returns the number of rows for pagination
   * @return int The number of rows
   */
  public function getRowCount()
  {
    return $this->rowCount;
  }

  /**
   * Returns the number of pages
   * which result from the given number of rows and the rows per page.
   * @return int The number of pages
   */
  public function getPageCount()
  {
    return $this->getLastPage() + 1;
  }

  /**
   * Returns the number of rows which will be displayed on a page
   * @return int The rows displayed on a page
   */
  public function getRowsPerPage()
  {
    return $this->rowsPerPage;
  }

  /**
   * Returns the current pagination position.
   *
   * When the parameter pageNo is given, the cursor for the given page is returned.
   * When no parameter is given, the cursor for active page is returned.
   *
   * @param int $pageNo
   */
  public function getCursor($pageNo = null)
  {
    if(is_null($pageNo))
    {
      $cursor = rex_request($this->cursorName, 'int', 0);
    }
    else
    {
      $cursor = $pageNo * $this->rowsPerPage;
    }

    // $cursor innerhalb des zulässigen Zahlenbereichs?
    if($cursor < 0)
      $cursor = 0;
    else if($cursor > $this->rowCount)
      $cursor = (int) ($this->rowCount / $this->rowsPerPage) * $this->rowsPerPage;

    return $cursor;
  }

  /**
   * Returns the name of the parameter which is used for pagination
   * @return string The name of the cursor
   */
  public function getCursorName()
  {
    return $this->cursorName;
  }

  /**
   * Returns the first page for pagination
   * @return int The first page number
   */
  public function getFirstPage()
  {
    return 0;
  }

  /**
   * Returns the previous page in respect to the current page
   * @return int The previous page number
   */
  public function getPrevPage()
  {
    $prevPage = $this->getCurrentPage() - 1;
    if($prevPage < $this->getFirstPage())
    {
      return $this->getFirstPage();
    }
    return $prevPage;
  }

  /**
   * Returns the number of the current page
   * @return int The current page number
   */
  public function getCurrentPage()
  {
    $cursor = rex_request($this->cursorName, 'int', null);

    if(is_null($cursor))
    {
      return $this->getFirstPage();
    }

    return (int) ($cursor / $this->rowsPerPage);
  }

  /**
   * Returns the number of the next page in respect to the current page
   * @return int The next page number
   */
  public function getNextPage()
  {
    $nextPage = $this->getCurrentPage() + 1;
    if($nextPage > $this->getLastPage())
    {
      return $this->getLastPage();
    }
    return $nextPage;
  }

  /**
   * Return the page number of the last page
   * @return int the last page number
   */
  public function getLastPage()
  {
    return ceil($this->rowCount / $this->rowsPerPage);
  }

  /**
   * Checks wheter the given page number is the active/current page
   * @param boolean True when the given pageNo is the current page, otherwise False
   */
  public function isActivePage($pageNo)
  {
    return $pageNo == $this->getCurrentPage();
  }
}
