<?php

// Nötige Konstanten
define('REX_LIST_OPT_SORT', 0);

/**
 * Klasse zum erstellen von Listen
 *
 * @package redaxo4
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

class rex_list
{
  var $query;
  var $sql;
  var $debug;
  var $noRowsMessage;

  // --------- List Attributes
  var $name;
  var $params;
  var $rows;

  // --------- Form Attributes
  var $formAttributes;

  // --------- Column Attributes
  var $columnNames;
  var $columnLabels;
  var $columnFormates;
  var $columnOptions;
  var $columnAttributes;
  var $columnLayouts;
  var $columnParams;
  var $columnDisabled;

  // --------- Layout, Default
  var $defaultColumnLayout;

  // --------- Table Attributes
  var $caption;
  var $tableAttributes;
  var $tableColumnGroups;

  // --------- Link Attributes
  var $linkAttributes;

  // --------- Pagination Attributes
  var $rowsPerPage;

  /**
   * Erstellt ein rex_list Objekt
   *
   * @param $query SELECT Statement
   * @param $rowsPerPage Anzahl der Elemente pro Zeile
   * @param $listName Name der Liste
   */
  function rex_list($query, $rowsPerPage = 30, $listName = null, $debug = false)
  {
    global $REX, $I18N;

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
    $this->noRowsMessage = $I18N->msg('list_no_rows');

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
    $this->rowsPerPage = $rowsPerPage;

    // --------- Load Data
    $this->sql->setQuery($this->prepareQuery($query));
    if($this->sql->hasError())
    {
      echo rex_warning($this->sql->getError());
      return;
    }

    foreach($this->sql->getFieldnames() as $columnName)
      $this->columnNames[] = $columnName;

    // --------- Load Env
    if($REX['REDAXO'])
      $this->loadBackendConfig();

    $this->init();
  }

  function factory($query, $rowsPerPage = 30, $listName = null, $debug = false, $class = null)
  {
    // keine spezielle klasse angegeben -> default klasse verwenden?
    if(!$class)
    {
      // ----- EXTENSION POINT
      $class = rex_register_extension_point('REX_LIST_CLASSNAME', 'rex_list',
        array(
          'query'       => $query,
          'rowsPerPage' => $rowsPerPage,
          'listName'    => $listName,
          'debug'       => $debug
        )
      );
    }

    return new $class($query, $rowsPerPage, $listName, $debug);
  }

  function init()
  {
    // nichts tun
  }

  // ---------------------- setters/getters

  /**
   * Gibt den Namen es Formulars zurück
   *
   * @return string
   */
  function getName()
  {
    return $this->name;
  }

  /**
   * Gibt eine Status Nachricht zurück
   *
   * @return string
   */
  function getMessage()
  {
    return stripslashes(rex_request($this->getName().'_msg', 'string'));
  }

  /**
   * Gibt eine Warnung zurück
   *
   * @return string
   */
  function getWarning()
  {
    return stripslashes(rex_request($this->getName().'_warning', 'string'));
  }

  /**
   * Setzt die Caption/den Titel der Tabelle
   * Gibt den Namen es Formulars zurück
   *
   * @param $caption Caption/Titel der Tabelle
   */
  function setCaption($caption)
  {
    $this->caption = $caption;
  }

  /**
   * Gibt die Caption/den Titel der Tabelle zurück
   *
   * @return string
   */
  function getCaption()
  {
    return $this->caption;
  }

  function setNoRowsMessage($msg)
  {
    $this->noRowsMessage = $msg;
  }

  function getNoRowsMessage()
  {
    return $this->noRowsMessage;
  }

  function addParam($name, $value)
  {
    $this->params[$name] = $value;
  }

  function getParams()
  {
    return $this->params;
  }

  function loadBackendConfig()
  {
    $this->addParam('page', rex_request('page', 'string'));
    $this->addParam('subpage', rex_request('subpage', 'string'));
  }

  function addTableAttribute($attrName, $attrValue)
  {
    $this->tableAttributes[$attrName] = $attrValue;
  }

  function getTableAttributes()
  {
    return $this->tableAttributes;
  }

  function addFormAttribute($attrName, $attrValue)
  {
    $this->formAttributes[$attrName] = $attrValue;
  }

  function getFormAttributes()
  {
    return $this->formAttributes;
  }

  function addLinkAttribute($columnName, $attrName, $attrValue)
  {
    $this->linkAttributes[$columnName] = array($attrName => $attrValue);
  }

  function getLinkAttributes($column, $default = null)
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
  function addColumn($columnHead, $columnBody, $columnIndex = -1, $columnLayout = null)
  {
    // Bei negativem columnIndex, das Element am Ende anfügen
    if($columnIndex < 0)
      $columnIndex = count($this->columnNames);

    $this->columnNames = array_insert($this->columnNames, $columnIndex, array($columnHead));
    $this->setColumnFormat($columnHead, $columnBody);
    $this->setColumnLayout($columnHead, $columnLayout);
  }

  /**
   * Entfernt eine Spalte aus der Anzeige
   *
   * @param $columnName Name der Spalte
   */
  function removeColumn($columnName)
  {
    $this->columnDisabled[] = $columnName;
  }

  /**
   * Methode, um das Layout einer Spalte zu setzen
   *
   * @param $columnHead string Titel der Spalte
   * @param $columnLayout array Layout der Spalte
   */
  function setColumnLayout($columnHead, $columnLayout)
  {
    $this->columnLayout[$columnHead] = $columnLayout;
  }
  
  /**
   * Gibt das Layout einer Spalte zurück
   *
   * @param $columnName Name der Spalte
   */
  function getColumnLayout($columnName)
  {
    if (isset($this->columnLayout[$columnName]) && is_array($this->columnLayout[$columnName]))
      return $this->columnLayout[$columnName];

    return $this->defaultColumnLayout;
  }

  /**
   * Gibt die Layouts aller Spalten zurück
   */
  function getColumnLayouts()
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
  function getColumnName($columnIndex, $default = null)
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
  function getColumnNames()
  {
    return $this->columnNames;
  }

  /**
   * Setzt ein Label für eine Spalte
   *
   * @param $columnName Name der Spalte
   * @param $label Label für die Spalte
   */
  function setColumnLabel($columnName, $label)
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
  function getColumnLabel($columnName, $default = null)
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
  function setColumnFormat($columnName, $format_type, $format = '')
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
  function getColumnFormat($columnName, $default = null)
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
  function setColumnSortable($columnName)
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
  function setColumnOption($columnName, $option, $value)
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
  function getColumnOption($columnName, $option, $default = null)
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
  function hasColumnOption($columnName, $option)
  {
    return isset($this->columnOptions[$columnName][$option]);
  }

  /**
   * Verlinkt eine Spalte mit den übergebenen Parametern
   *
   * @param $columnName Name der Spalte
   * @param $params Array von Parametern
   */
  function setColumnParams($columnName, $params = array())
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
  function getColumnParams($columnName)
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
  function hasColumnParams($columnName)
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
  function addTableColumnGroup($columns, $columnGroupSpan = null)
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

  function _addTableColumnGroup($tableColumnGroup)
  {
    if(!is_array($tableColumnGroup))
      trigger_error('rex_list->addTableColumnGroup: Erwarte 1. Parameter als Array!', E_USER_ERROR);

    $this->tableColumnGroups[] = $tableColumnGroup;
  }

  function getTableColumnGroups()
  {
    return $this->tableColumnGroups;
  }

  /**
   * Fügt der zuletzte eingefügten TableColumnGroup eine weitere Spalte hinzu
   *
   * @param $width int Breite der Spalte
   * @param $span int Span der Spalte
   */
  function addTableColumn($width, $span = null)
  {
    $attributes = array('width' => $width);
    if($span) $attributes['span'] = $span;

    $this->_addTableColumn($attributes);
  }

  function _addTableColumn($tableColumn)
  {
    if(!is_array($tableColumn))
      trigger_error('rex_list->_addTableColumn: Erwarte 1. Parameter als Array!', E_USER_ERROR);

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
  function getUrl($params = array())
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
  function getParsedUrl($params = array())
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
  function prepareQuery($query)
  {
    $rowsPerPage = $this->getRowsPerPage();
    $startRow = $this->getStartRow();

    $sortColumn = $this->getSortColumn();
    if($sortColumn != '')
    {
      $sortType = $this->getSortType();

      if(strpos(strtoupper($query), ' ORDER BY ') === false)
        $query .= ' ORDER BY '. $sortColumn .' '. $sortType;
      else
        $query = preg_replace('/ORDER\sBY\s[^ ]*(\sasc|\sdesc)?/i', 'ORDER BY '. $sortColumn .' '. $sortType, $query);
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
  function getRows()
  {
    if(!$this->rows)
    {
      $sql = rex_sql::factory();
      $sql->debugsql = $this->debug;
      $sql->setQuery($this->query);
      $this->rows = $sql->getRows();
    }

    return $this->rows;
  }

  /**
   * Gibt die Anzahl der Zeilen pro Seite zurück
   *
   * @return int
   */
  function getRowsPerPage()
  {
    if(rex_request('list', 'string') == $this->getName())
    {
      $rowsPerPage = rex_request('items', 'int');

      // Fallback auf Default-Wert
      if($rowsPerPage <= 0)
        $rowsPerPage = $this->rowsPerPage;
      else
        $this->rowsPerPage = $rowsPerPage;
    }

    return $this->rowsPerPage;
  }

  /**
   * Gibt die Nummer der Zeile zurück, von der die Liste beginnen soll
   *
   * @return int
   */
  function getStartRow()
  {
    $start = 0;

    if(rex_request('list', 'string') == $this->getName())
    {
      $start = rex_request('start', 'int', 0);
      $rows = $this->getRows();

      // $start innerhalb des zulässigen Zahlenbereichs?
      if($start < 0)
        $start = 0;

      if($start > $rows)
        $start = (int) ($rows / $this->getRowsPerPage()) * $this->getRowsPerPage();
    }

    return $start;
  }

  /**
   * Gibt zurück, nach welcher Spalte sortiert werden soll
   *
   * @return string
   */
  function getSortColumn($default = null)
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
  function getSortType($default = null)
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
  function getPagination()
  {
    global $I18N;

    $start = $this->getStartRow();
    $rows = $this->getRows();
    $rowsPerPage = $this->getRowsPerPage();
    $pages = ceil($rows / $rowsPerPage);

    $s = '<ul class="rex-navi-paginate">'. "\n";
    $s .= '<li class="rex-navi-paginate-prev"><a href="'. $this->getUrl(array('start' => $start - $rowsPerPage)) .'" title="'. $I18N->msg('list_previous') .'"><span>'. $I18N->msg('list_previous') .'</span></a></li>';

    if($pages > 1)
    {
      for($i = 1; $i <= $pages; $i++)
      {
        $first = ($i - 1) * $rowsPerPage;
        $last = $i * $rowsPerPage;

        if($last > $rows)
          $last = $rows;

        $pageLink = $i;
        if($start != $first)
          $pageLink = '<a href="'. $this->getUrl(array('start' => $first)) .'"><span>'. $pageLink .'</span></a>';
        else
          $pageLink = '<a class="rex-active" href="'. $this->getUrl(array('start' => $first)) .'"><span>'. $pageLink .'</span></a>';

        $s .= '<li class="rex-navi-paginate-page">'. $pageLink .'</li>';
      }
    }
    $s .= '<li class="rex-navi-paginate-next"><a href="'. $this->getUrl(array('start' => $start + $rowsPerPage)) .'" title="'. $I18N->msg('list_next') .'"><span>'. $I18N->msg('list_next') .'</span></a></li>';
    $s .= '<li class="rex-navi-paginate-message"><span>'. $I18N->msg('list_rows_found', $this->getRows()) .'</span></li>';
    $s .= '</ul>'. "\n";

    return '<div class="rex-navi-paginate rex-toolbar"><div class="rex-toolbar-content">'.$s.'<div class="rex-clearer"></div></div></div>';
  }

  /**
   * Gibt den Footer der Liste zurück
   *
   * @return string
   */
  function getFooter()
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
  function getHeader()
  {
    $s = '';

    if($this->getRows() > $this->getRowsPerPage())
      $s = $this->getPagination();

    return $s;
  }

  // ---------------------- Generate Output

  function replaceVariable($string, $varname)
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
  function replaceVariables($value)
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

  function isCustomFormat($format)
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
  function formatValue($value, $format, $escape)
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

  function _getAttributeString($array)
  {
    $s = '';

    foreach($array as $name => $value)
      $s .= ' '. $name .'="'. $value .'"';

    return $s;
  }

  function getColumnLink($columnName, $columnValue, $params = array())
  {
    return '<a href="'. $this->getParsedUrl(array_merge($this->getColumnParams($columnName), $params)) .'"'. $this->_getAttributeString($this->getLinkAttributes($columnName, array())) .'>'. $columnValue .'</a>';
  }

  function getValue($colname)
  {
    return $this->sql->getValue($colname);
  }

  /**
   * Erstellt den Tabellen Quellcode
   *
   * @return string
   */
  function get()
  {
    global $I18N;

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
      $s .= rex_warning($warning). "\n";
    }
    else if($message != '')
    {
      $s .= rex_info($message). "\n";
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
        $columnHead = '<a href="'. $this->getUrl(array('start' => $this->getStartRow(),'sort' => $columnName, 'sorttype' => $columnSortType)) .'">'. $columnHead .'</a>';
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
      $maxRows = $nbRows - $this->getStartRow();

      $s .= '    <tbody>'. "\n";
      for($i = 0; $i < $this->getRowsPerPage() && $i < $maxRows; $i++)
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

  function show()
  {
    echo $this->get();
  }
}