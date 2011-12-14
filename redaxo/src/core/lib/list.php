<?php

// Nötige Konstanten
define('REX_LIST_OPT_SORT', 0);

/**
 * Klasse zum erstellen von Listen
 *
 * @package redaxo5
 * @version svn:$Id$
 */

/*
Beispiel:

$list = rex_list::factory('SELECT id,name FROM rex_article');
$list->setColumnFormat('id', 'date');
$list->setColumnLabel('name', 'Artikel-Name');
$list->setColumnSortable('name');
$list->addColumn('testhead','###id### - ###name###',-1);
$list->addColumn('testhead2','testbody2');
$list->setCaption('thomas macht das css');
$list->show();


Beispiel für Custom Callbacks mit Parametern:

function abc($params)
{
  // $params['subject']  ist das SQL Objekt der aktuellen Zeile
  // $params['params']   sind die Parameter die du selbst angibst

  return $xyz; // Rückgabewert = Wert der in der liste erscheint - kein htmlspechialchars!
}

$list->setColumnFormat('id', 'custom',
  array(
    'abc',
    array('xy' => 'abc', '123' => '45')
  )
);

*/

class rex_list extends rex_factory implements rex_url_provider
{
  private $query;
  private $sql;
  private $debug;
  private $noRowsMessage;

  // --------- List Attributes
  private $name;
  private $params;
  private $rows;

  // --------- Form Attributes
  private $formAttributes;

  // --------- Column Attributes
  private $columnNames;
  private $columnLabels;
  private $columnFormates;
  private $columnOptions;
  private $columnAttributes;
  private $columnLayouts;
  private $columnParams;
  private $columnDisabled;

  // --------- Layout, Default
  private $defaultColumnLayout;

  // --------- Table Attributes
  private $caption;
  private $tableAttributes;
  private $tableColumnGroups;

  // --------- Link Attributes
  private $linkAttributes;

  // --------- Pagination Attributes
  private $pager;

  /**
   * Erstellt ein rex_list Objekt
   *
   * @param $query SELECT Statement
   * @param $rowsPerPage Anzahl der Elemente pro Zeile
   * @param $listName Name der Liste
   */
  protected function __construct($query, $rowsPerPage = 30, $listName = null, $debug = false)
  {
    // --------- Validation
    if(!$listName) $listName = md5($query);

    // --------- List Attributes
    $this->query = $query;
    $this->sql = rex_sql::factory();
    $this->debug = $debug;
    $this->sql->debugsql =& $this->debug;
    $this->name = $listName;
    $this->caption = '';
    $this->rows = 0;
    $this->params = array();
    $this->tableAttributes = array();
    $this->noRowsMessage = rex_i18n::msg('list_no_rows');

    // --------- Form Attributes
    $this->formAttributes = array();

    // --------- Column Attributes
    $this->columnLabels = array();
    $this->columnFormates = array();
    $this->columnParams = array();
    $this->columnOptions = array();
    $this->columnAttributes = array();
    $this->columnLayouts = array();
    $this->columnDisabled = array();

    // --------- Default
    $this->defaultColumnLayout = array('<th>###VALUE###</th>','<td>###VALUE###</td>');

    // --------- Table Attributes
    $this->tableAttributes = array();
    $this->tableColumnGroups = array();

    // --------- Link Attributes
    $this->linkAttributes = array();

    // --------- Pagination Attributes
    $this->pager = new rex_pager($this->getRows(), $rowsPerPage);

    // --------- Load Data
    $this->sql->setQuery($this->prepareQuery($query));

    foreach($this->sql->getFieldnames() as $columnName)
      $this->columnNames[] = $columnName;

    // --------- Load Env
    if(rex::isBackend())
      $this->loadBackendConfig();

    $this->init();
  }

  static public function factory($query, $rowsPerPage = 30, $listName = null, $debug = false)
  {
    $class = self::getFactoryClass();
    return new $class($query, $rowsPerPage, $listName, $debug);
  }

  public function init()
  {
    // nichts tun
  }

  // ---------------------- setters/getters

  /**
   * Gibt den Namen es Formulars zurück
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Gibt eine Status Nachricht zurück
   *
   * @return string
   */
  public function getMessage()
  {
    return rex_request($this->getName().'_msg', 'string');
  }

  /**
   * Gibt eine Warnung zurück
   *
   * @return string
   */
  public function getWarning()
  {
    return rex_request($this->getName().'_warning', 'string');
  }

  /**
   * Setzt die Caption/den Titel der Tabelle
   * Gibt den Namen es Formulars zurück
   *
   * @param $caption Caption/Titel der Tabelle
   */
  public function setCaption($caption)
  {
    $this->caption = $caption;
  }

  /**
   * Gibt die Caption/den Titel der Tabelle zurück
   *
   * @return string
   */
  public function getCaption()
  {
    return $this->caption;
  }

  public function setNoRowsMessage($msg)
  {
    $this->noRowsMessage = $msg;
  }

  public function getNoRowsMessage()
  {
    return $this->noRowsMessage;
  }

  public function addParam($name, $value)
  {
    $this->params[$name] = $value;
  }

  public function getParams()
  {
    return $this->params;
  }

  protected function loadBackendConfig()
  {
    $this->addParam('page', rex_request('page', 'string'));
    $this->addParam('subpage', rex_request('subpage', 'string'));
  }

  public function addTableAttribute($attrName, $attrValue)
  {
    $this->tableAttributes[$attrName] = $attrValue;
  }

  public function getTableAttributes()
  {
    return $this->tableAttributes;
  }

  public function addFormAttribute($attrName, $attrValue)
  {
    $this->formAttributes[$attrName] = $attrValue;
  }

  public function getFormAttributes()
  {
    return $this->formAttributes;
  }

  public function addLinkAttribute($columnName, $attrName, $attrValue)
  {
    $this->linkAttributes[$columnName] = array($attrName => $attrValue);
  }

  public function getLinkAttributes($column, $default = null)
  {
    return isset($this->linkAttributes[$column]) ? $this->linkAttributes[$column] : $default;
  }

  // ---------------------- Column setters/getters/etc

  /**
   * Methode, um eine Spalte einzufügen
   *
   * @param $columnHead string Titel der Spalte
   * @param $columnBody string Text/Format der Spalte
   * @param $columnIndex int Stelle, an der die neue Spalte erscheinen soll
   * @param $columnLayout array Layout der Spalte
   */
  public function addColumn($columnHead, $columnBody, $columnIndex = -1, $columnLayout = null)
  {
    // Bei negativem columnIndex, das Element am Ende anfügen
    if($columnIndex < 0)
      $columnIndex = count($this->columnNames);

    array_splice($this->columnNames, $columnIndex, 0, array(array($columnHead)));
    $this->setColumnFormat($columnHead, $columnBody);
    $this->setColumnLayout($columnHead, $columnLayout);
  }

  /**
   * Entfernt eine Spalte aus der Anzeige
   *
   * @param $columnName Name der Spalte
   */
  public function removeColumn($columnName)
  {
    $this->columnDisabled[] = $columnName;
  }

  /**
   * Methode, um das Layout einer Spalte zu setzen
   *
   * @param $columnHead string Titel der Spalte
   * @param $columnLayout array Layout der Spalte
   */
  public function setColumnLayout($columnHead, $columnLayout)
  {
    $this->columnLayouts[$columnHead] = $columnLayout;
  }

  /**
   * Gibt das Layout einer Spalte zurück
   *
   * @param $columnName Name der Spalte
   */
  public function getColumnLayout($columnName)
  {
    if (isset($this->columnLayouts[$columnName]) && is_array($this->columnLayouts[$columnName]))
      return $this->columnLayouts[$columnName];

    return $this->defaultColumnLayout;
  }

  /**
   * Gibt die Layouts aller Spalten zurück
   */
  public function getColumnLayouts()
  {
    return $this->columnLayouts;
  }

  /**
   * Gibt den Namen einer Spalte zurück
   *
   * @param $columnIndex Nummer der Spalte
   * @param $default Defaultrückgabewert, falls keine Spalte mit der angegebenen Nummer vorhanden ist
   *
   * @return string|null
   */
  public function getColumnName($columnIndex, $default = null)
  {
    if(isset($this->columnNames[$columnIndex]))
      return $this->columnNames[$columnIndex];

    return $default;
  }

  /**
   * Gibt alle Namen der Spalten als Array zurück
   *
   * @return array
   */
  public function getColumnNames()
  {
    return $this->columnNames;
  }

  /**
   * Setzt ein Label für eine Spalte
   *
   * @param $columnName Name der Spalte
   * @param $label Label für die Spalte
   */
  public function setColumnLabel($columnName, $label)
  {
    $this->columnLabels[$columnName] = $label;
  }

  /**
   * Gibt das Label der Spalte zurück, falls gesetzt.
   *
   * Falls nicht vorhanden und der Parameter $default auf null steht,
   * wird der Spaltenname zurückgegeben
   *
   * @param $columnName Name der Spalte
   * @param $default Defaultrückgabewert, falls kein Label gesetzt ist
   *
   * @return string|null
   */
  public function getColumnLabel($columnName, $default = null)
  {
    if(isset($this->columnLabels[$columnName]))
      return $this->columnLabels[$columnName];

    return $default === null ? $columnName : $default;
  }

  /**
   * Setzt ein Format für die Spalte
   *
   * @param $columnName Name der Spalte
   * @param $format_type Formatierungstyp
   * @param $format Zu verwendentes Format
   */
  public function setColumnFormat($columnName, $format_type, $format = '')
  {
    $this->columnFormates[$columnName] = array($format_type, $format);
  }

  /**
   * Gibt das Format für eine Spalte zurück
   *
   * @param $columnName Name der Spalte
   * @param $default Defaultrückgabewert, falls keine Formatierung gesetzt ist
   *
   * @return string|null
   */
  public function getColumnFormat($columnName, $default = null)
  {
    if(isset($this->columnFormates[$columnName]))
      return $this->columnFormates[$columnName];

    return $default;
  }

  /**
   * Markiert eine Spalte als sortierbar
   *
   * @param $columnName Name der Spalte
   */
  public function setColumnSortable($columnName)
  {
    $this->setColumnOption($columnName, REX_LIST_OPT_SORT, true);
  }

  /**
   * Setzt eine Option für eine Spalte
   * (z.b. Sortable,..)
   *
   * @param $columnName Name der Spalte
   * @param $option Name/Id der Option
   * @param $value Wert der Option
   */
  public function setColumnOption($columnName, $option, $value)
  {
    $this->columnOptions[$columnName][$option] = $value;
  }

  /**
   * Gibt den Wert einer Option für eine Spalte zurück
   *
   * @param $columnName Name der Spalte
   * @param $option Name/Id der Option
   * @param $default Defaultrückgabewert, falls die Option nicht gesetzt ist
   *
   * @return mixed|null
   */
  public function getColumnOption($columnName, $option, $default = null)
  {
    if($this->hasColumnOption($columnName, $option))
    {
      return $this->columnOptions[$columnName][$option];
    }
    return $default;
  }

  /**
   * Gibt zurück, ob für eine Spalte eine Option gesetzt wurde
   *
   * @param $columnName Name der Spalte
   * @param $option Name/Id der Option
   * @param $default Defaultrückgabewert, falls die Option nicht gesetzt ist
   *
   * @return boolean
   */
  public function hasColumnOption($columnName, $option)
  {
    return isset($this->columnOptions[$columnName][$option]);
  }

  /**
   * Verlinkt eine Spalte mit den übergebenen Parametern
   *
   * @param $columnName Name der Spalte
   * @param $params Array von Parametern
   */
  public function setColumnParams($columnName, $params = array())
  {
    if(!is_array($params))
      trigger_error('rex_list->setColumnParams: Erwarte 2. Parameter als Array!', E_USER_ERROR);

    $this->columnParams[$columnName] = $params;
  }

  /**
   * Gibt die Parameter für eine Spalte zurück
   *
   * @param $columnName Name der Spalte
   *
   * @return array
   */
  public function getColumnParams($columnName)
  {
    return $this->columnParams[$columnName];
  }

  /**
   * Gibt zurück, ob Parameter für eine Spalte existieren
   *
   * @param $columnName Name der Spalte
   *
   * @return boolean
   */
  public function hasColumnParams($columnName)
  {
    return isset($this->columnParams[$columnName]) && is_array($this->columnParams[$columnName]) && count($this->columnParams[$columnName]) > 0;
  }

  // ---------------------- TableColumnGroup setters/getters/etc

  /**
   * Methode um eine Colgroup einzufügen
   *
   * Beispiel 1:
   *
   * $list->addTableColumnGroup(array(40, 240, 140));
   *
   * Beispiel 2:
   *
   * $list->addTableColumnGroup(
   *   array(
   *     array('width' => 40),
   *     array('width' => 140, 'span' => 2),
   *     array('width' => 240),
   *   )
   * );
   *
   * @param $columns array Array von Spalten
   * @param $columnGroupSpan integer Span der Columngroup
   */
  public function addTableColumnGroup($columns, $columnGroupSpan = null)
  {
    if(!is_array($columns))
      trigger_error('rex_list->addTableColumnGroup: Erwarte 1. Parameter als Array!', E_USER_ERROR);

    $tableColumnGroup = array('columns' => array());
    if($columnGroupSpan) $tableColumnGroup['span'] = $columnGroupSpan;
    $this->_addTableColumnGroup($tableColumnGroup);

    if(isset($columns[0]) && is_scalar($columns[0]))
    {
      // array(10,50,100,150) notation
      foreach($columns as $column)
        $this->addTableColumn($column);
    }
    else
    {
      // array(array('width'=>100,'span'=>2), array(...), array(...)) notation
      foreach($columns as $column)
        $this->_addTableColumn($column);
    }
  }

  private function _addTableColumnGroup(array $tableColumnGroup)
  {
    $this->tableColumnGroups[] = $tableColumnGroup;
  }

  public function getTableColumnGroups()
  {
    return $this->tableColumnGroups;
  }

  /**
   * Fügt der zuletzte eingefügten TableColumnGroup eine weitere Spalte hinzu
   *
   * @param $width int Breite der Spalte
   * @param $span int Span der Spalte
   */
  public function addTableColumn($width, $span = null)
  {
    $attributes = array('width' => $width);
    if($span) $attributes['span'] = $span;

    $this->_addTableColumn($attributes);
  }

  private function _addTableColumn(array $tableColumn)
  {
    if(!isset($tableColumn['width']))
      trigger_error('rex_list->_addTableColumn: Erwarte index width!', E_USER_ERROR);

    $lastIndex = count($this->tableColumnGroups) - 1;

    if($lastIndex < 0)
    {
      // Falls noch keine TableColumnGroup vorhanden, eine leere anlegen!
      $this->addTableColumnGroup(array());
      $lastIndex++;
    }

    $groupColumns = $this->tableColumnGroups[$lastIndex]['columns'];
    $groupColumns[] = $tableColumn;
    $this->tableColumnGroups[$lastIndex]['columns'] = $groupColumns;
  }

  // ---------------------- Url generation

  /**
   * Gibt eine Url zurück, die die Parameter $params enthält
   * Dieser Url werden die Standard rexList Variablen zugefügt
   *
   * @return string
   */
  public function getUrl(array $params = array())
  {
    $params = array_merge($this->getParams(), $params);

// aendern der items pro seite aktuell nicht vorgesehen
//    if(!isset($params['items']))
//    {
//      $params['items'] = $this->getRowsPerPage();
//    }
    if(!isset($params['sort']))
    {
      $sortColumn = $this->getSortColumn();
      if($sortColumn != null)
      {
        $params['sort'] = $sortColumn;
        $params['sorttype'] = $this->getSortType();
      }
    }

    $paramString = '';
    foreach($params as $name => $value)
    {
      if(is_array($value))
      {
        foreach($value as $v)
      	{
          $paramString .= '&'. $name .'='. $v;
      	}
      }else
      {
        $paramString .= '&'. $name .'='. $value;
      }
    }
    return str_replace('&', '&amp;', 'index.php?list='. $this->getName() . $paramString);
  }

  /**
   * Gibt eine Url zurück, die die Parameter $params enthält
   * Dieser Url werden die Standard rexList Variablen zugefügt
   *
   * Innerhalb dieser Url werden variablen ersetzt
   *
   * @see #replaceVariable, #replaceVariables
   * @return string
   */
  public function getParsedUrl($params = array())
  {
    return $this->replaceVariables($this->getUrl($params));
  }

  // ---------------------- Pagination

  /**
   * Prepariert das SQL Statement vorm anzeigen der Liste
   *
   * @param $query SQL Statement
   *
   * @return string
   */
  protected function prepareQuery($query)
  {
    $rowsPerPage = $this->pager->getRowsPerPage();
    $startRow = $this->pager->getCursor();

    $sortColumn = $this->getSortColumn();
    if($sortColumn != '')
    {
      $sortType = $this->getSortType();

      if(strpos(strtoupper($query), ' ORDER BY ') === false)
        $query .= ' ORDER BY `'. $sortColumn .'` '. $sortType;
      else
        $query = preg_replace('/ORDER\sBY\s[^ ]*(\sasc|\sdesc)?/i', 'ORDER BY `'. $sortColumn .'` '. $sortType, $query);
    }

    if(strpos(strtoupper($query), ' LIMIT ') === false)
      $query .= ' LIMIT '. $startRow .','. $rowsPerPage;

    return $query;
  }

  /**
   * Gibt die Anzahl der Zeilen zurück, welche vom ursprüngliche SQL Statement betroffen werden
   *
   * @return int
   */
  public function getRows()
  {
    if(!$this->rows)
    {
      // TODO add SQL_CALC_FOUND_ROWS
      $sql = rex_sql::factory();
      $sql->debugsql = $this->debug;
      $sql->setQuery($this->query);
      $this->rows = $sql->getRows();
    }

    return $this->rows;
  }

  /**
   * Returns the pager for this list
   *
   * @return rex_pager
   */
  public function getPager()
  {
    return $this->pager;
  }

  /**
   * Gibt zurück, nach welcher Spalte sortiert werden soll
   *
   * @return string
   */
  public function getSortColumn($default = null)
  {
    if(rex_request('list', 'string') == $this->getName())
    {
      return rex_request('sort','string', $default);
    }
    return $default;
  }

  /**
   * Gibt zurück, in welcher Art und Weise sortiert werden soll (ASC/DESC)
   *
   * @return string
   */
  public function getSortType($default = null)
  {
    if(rex_request('list', 'string') == $this->getName())
    {
      $sortType = rex_request('sorttype','string');

      if(in_array($sortType, array('asc', 'desc')))
        return $sortType;
    }
    return $default;
  }

  /**
   * Gibt die Navigation der Liste zurück
   *
   * @return string
   */
  protected function getPagination()
  {
    $fragment = new rex_fragment();
    $fragment->setVar('urlprovider', $this);
    $fragment->setVar('pager', $this->pager);
    return $fragment->parse('pagination');
  }

  /**
   * Gibt den Footer der Liste zurück
   *
   * @return string
   */
  public function getFooter()
  {
    $s = '';
    /*
    $s .= '      <tr>'. "\n";
    $s .= '        <td colspan="'. count($this->getColumnNames()) .'"><input type="text" name="items" value="'. $this->getRowsPerPage() .'" maxlength="2" /><input type="submit" value="Anzeigen" /></td>'. "\n";
    $s .= '      </tr>'. "\n";
    */
    return $s;
  }

  /**
   * Gibt den Header der Liste zurück
   *
   * @return string
   */
  public function getHeader()
  {
    $s = '';
    $s .= $this->getPagination();

    return $s;
  }

  // ---------------------- Generate Output

  public function replaceVariable($string, $varname)
  {
    return str_replace('###'. $varname .'###', htmlspecialchars($this->getValue($varname)), $string);
  }

  /**
   * Ersetzt alle Variablen im Format ###<Spaltenname>###.
   *
   * @param $value Zu durchsuchender String
   * @param $columnNames Zu suchende Spaltennamen
   *
   * @return string
   */
  public function replaceVariables($value)
  {
    if(strpos($value, '###') === false)
      return $value;

    $columnNames = $this->getColumnNames();

    if(is_array($columnNames))
    {
      foreach($columnNames as $columnName)
      {
        // Spalten, die mit addColumn eingefügt wurden
        if(is_array($columnName))
          continue;

        $value = $this->replaceVariable($value, $columnName);
      }
    }
    return $value;
  }

  public function isCustomFormat($format)
  {
    return is_array($format) && isset($format[0]) && $format[0] == 'custom';
  }

  /**
   * Formatiert einen übergebenen String anhand der rexFormatter Klasse
   *
   * @param $value Zu formatierender String
   * @param $format Array mit den Formatierungsinformationen
   * @param $escape Flag, Ob escapen von $value erlaubt ist
   *
   * @return string
   */
  public function formatValue($value, $format, $escape)
  {
    if(is_array($format))
    {
      // Callbackfunktion -> Parameterliste aufbauen
      if($this->isCustomFormat($format))
      {
        $format[1] = array($format[1], array('list' => $this, 'value' => $value, 'format' => $format[0], 'escape' => $escape));
      }

      $value = rex_formatter::format($value, $format[0], $format[1]);
    }

    // Nur escapen, wenn formatter aufgerufen wird, der kein html zurückgeben können soll
    if($escape && !$this->isCustomFormat($format) && $format[0] != 'rexmedia' && $format[0] != 'rexurl')
      $value = htmlspecialchars($value);

    return $value;
  }

  protected function _getAttributeString($array)
  {
    $s = '';

    foreach($array as $name => $value)
      $s .= ' '. $name .'="'. $value .'"';

    return $s;
  }

  public function getColumnLink($columnName, $columnValue, $params = array())
  {
    return '<a href="'. $this->getParsedUrl(array_merge($this->getColumnParams($columnName), $params)) .'"'. $this->_getAttributeString($this->getLinkAttributes($columnName, array())) .'>'. $columnValue .'</a>';
  }

  public function getValue($colname)
  {
    return $this->sql->getValue($colname);
  }

  /**
   * Erstellt den Tabellen Quellcode
   *
   * @return string
   */
  public function get()
  {
    $s = "\n";

    // Form vars
    $this->addFormAttribute('action', $this->getUrl());
    $this->addFormAttribute('method', 'post');

    // Table vars
    $caption = $this->getCaption();
    $tableColumnGroups = $this->getTableColumnGroups();
    $this->addTableAttribute('class', 'rex-table');

    // Columns vars
    $columnFormates = array();
    $columnNames = array_diff($this->getColumnNames(), $this->columnDisabled);

    // List vars
    $sortColumn = $this->getSortColumn();
    $sortType = $this->getSortType();
    $warning = $this->getWarning();
    $message = $this->getMessage();
    $nbRows = $this->getRows();

    $header = $this->getHeader();
    $footer = $this->getFooter();

    if($warning != '')
    {
      $s .= rex_view::warning($warning). "\n";
    }
    else if($message != '')
    {
      $s .= rex_view::info($message). "\n";
    }

    if($header != '')
    {
      $s .= $header . "\n";
    }

    $s .= '<form'. $this->_getAttributeString($this->getFormAttributes()) .'>'. "\n";
    $s .= '  <table'. $this->_getAttributeString($this->getTableAttributes()) .'>'. "\n";

    if($caption != '')
    {
      $s .= '    <caption>'. htmlspecialchars($caption) .'</caption>'. "\n";
    }

    if(count($tableColumnGroups) > 0)
    {
      foreach($tableColumnGroups as $tableColumnGroup)
      {
        $tableColumns = $tableColumnGroup['columns'];
        unset($tableColumnGroup['columns']);

        $s .= '    <colgroup'. $this->_getAttributeString($tableColumnGroup) .'>'. "\n";

        foreach($tableColumns as $tableColumn)
        {
          $s .= '      <col'. $this->_getAttributeString($tableColumn) .' />'. "\n";
        }

        $s .= '    </colgroup>'. "\n";
      }
    }

    $s .= '    <thead>'. "\n";
    $s .= '      <tr>'. "\n";
    foreach($columnNames as $columnName)
    {
      // Spalten, die mit addColumn eingefügt wurden
      if(is_array($columnName))
        $columnName = $columnName[0];

      $columnHead = $this->getColumnLabel($columnName);
      if($this->hasColumnOption($columnName, REX_LIST_OPT_SORT))
      {
        $columnSortType = $columnName == $sortColumn && $sortType == 'desc' ? 'asc' : 'desc';
        $columnHead = '<a href="'. $this->getUrl(array('start' => $this->pager->getCursor(),'sort' => $columnName, 'sorttype' => $columnSortType)) .'">'. $columnHead .'</a>';
      }

      $layout = $this->getColumnLayout($columnName);
      $s .= '        '. str_replace('###VALUE###', $columnHead, $layout[0])."\n";

      // Formatierungen hier holen, da diese Schleife jede Spalte nur einmal durchläuft
      $columnFormates[$columnName] = $this->getColumnFormat($columnName);
    }
    $s .= '      </tr>'. "\n";
    $s .= '    </thead>'. "\n";

    if($footer != '')
    {
      $s .= '    <tfoot>'. "\n";
      $s .= $footer;
      $s .= '    </tfoot>'. "\n";
    }

    if($nbRows > 0)
    {
      $maxRows = $nbRows - $this->pager->getCursor();

      $s .= '    <tbody>'. "\n";
      for($i = 0; $i < $this->pager->getRowsPerPage() && $i < $maxRows; $i++)
      {
        $s .= '      <tr>'. "\n";
        foreach($columnNames as $columnName)
        {
          // Spalten, die mit addColumn eingefügt wurden
          if(is_array($columnName))
          {
            // Nur hier sind Variablen erlaubt
            $columnName = $columnName[0];
            $columnValue = $this->formatValue($columnFormates[$columnName][0], $columnFormates[$columnName], false);
          }
          // Spalten aus dem ResultSet
          else
          {
            $columnValue = $this->formatValue($this->getValue($columnName), $columnFormates[$columnName], true);
          }

          if(!$this->isCustomFormat($columnFormates[$columnName]) && $this->hasColumnParams($columnName))
          {
            $columnValue = $this->getColumnLink($columnName, $columnValue);
          }

          $layout = $this->getColumnLayout($columnName);
          $columnValue = str_replace('###VALUE###', $columnValue, $layout[1]);
          $columnValue = $this->replaceVariables($columnValue);
          $s .= '        '. $columnValue ."\n";
        }
        $s .= '      </tr>'. "\n";

        $this->sql->next();
      }
      $s .= '    </tbody>'. "\n";
    }
    else
    {
      $s .= '<tr><td colspan="'. count($columnNames) .'">'. $this->getNoRowsMessage() .'</td></tr>';
    }

    $s .= '  </table>'. "\n";
    $s .= '</form>'. "\n";

    return $s;
  }

  public function show()
  {
    echo $this->get();
  }
}