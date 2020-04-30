<?php

/**
 * Klasse zur Verbindung und Interatkion mit der Datenbank.
 *
 * see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
 *
 * @package redaxo\core\sql
 */
class rex_sql implements Iterator
{
    use rex_factory_trait;

    public const MYSQL = 'MySQL';
    public const MARIADB = 'MariaDB';

    public const ERROR_VIOLATE_UNIQUE_KEY = 1062;

    /**
     * Default SQL datetime format.
     */
    public const FORMAT_DATETIME = 'Y-m-d H:i:s';

    /**
     * Controls query buffering.
     *
     * View `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` for more details.
     */
    public const OPT_BUFFERED = 'buffered';

    /** @var bool */
    protected $debug; // debug schalter
    /** @var array */
    protected $values; // Werte von setValue
    /** @var array */
    protected $rawValues; // Werte von setRawValue
    /** @var string[]|null */
    protected $fieldnames; // Spalten im ResultSet
    /** @var string[]|null */
    protected $rawFieldnames;
    /** @var string[]|null */
    protected $tablenames; // Tabelle im ResultSet
    /** @var array|null */
    protected $lastRow; // Wert der zuletzt gefetchten zeile
    /** @var string */
    protected $table; // Tabelle setzen

    /**
     * Where condition as string or as nested array (see `setWhere` for examples).
     *
     * @var null|string|array
     */
    protected $wherevar;

    /**
     * Params for where condition.
     *
     * @var array
     */
    protected $whereParams = [];

    /** @var int */
    protected $rows; // anzahl der treffer
    /** @var int */
    protected $counter; // pointer
    /** @var string */
    protected $query; // Die Abfrage
    /** @var array */
    protected $params; // Die Abfrage-Parameter
    /** @var int */
    protected $DBID; // ID der Verbindung

    /** @var self[] */
    protected $records;

    /** @var PDOStatement|null */
    protected $stmt;

    /** @var PDO[] */
    protected static $pdo = [];

    /**
     * @param int $DBID
     *
     * @throws rex_sql_exception
     */
    protected function __construct($DBID = 1)
    {
        $this->debug = false;
        $this->flush();
        $this->selectDB($DBID);
    }

    /**
     * Stellt die Verbindung zur Datenbank her.
     *
     * @param int $DBID
     *
     * @throws rex_sql_exception
     */
    protected function selectDB($DBID)
    {
        $this->DBID = $DBID;

        try {
            if (!isset(self::$pdo[$DBID])) {
                $options = [];
                $dbconfig = rex::getProperty('db');

                if (isset($dbconfig[$DBID]['ssl_key'], $dbconfig[$DBID]['ssl_cert'], $dbconfig[$DBID]['ssl_ca'])) {
                    $options = [
                        PDO::MYSQL_ATTR_SSL_KEY => $dbconfig[$DBID]['ssl_key'],
                        PDO::MYSQL_ATTR_SSL_CERT => $dbconfig[$DBID]['ssl_cert'],
                        PDO::MYSQL_ATTR_SSL_CA => $dbconfig[$DBID]['ssl_ca'],
                    ];
                }

                $conn = self::createConnection(
                    $dbconfig[$DBID]['host'],
                    $dbconfig[$DBID]['name'],
                    $dbconfig[$DBID]['login'],
                    $dbconfig[$DBID]['password'],
                    $dbconfig[$DBID]['persistent'],
                    $options
                );
                self::$pdo[$DBID] = $conn;

                // ggf. Strict Mode abschalten
                $this->setQuery('SET SESSION SQL_MODE="", NAMES utf8mb4');
            }
        } catch (PDOException $e) {
            throw new rex_sql_exception('Could not connect to database', $e, $this);
        }
    }

    /**
     * @param string $host
     * @param string $database
     * @param string $login
     * @param string $password
     * @param bool   $persistent
     *
     * @return PDO
     */
    protected static function createConnection($host, $database, $login, $password, $persistent = false, array $options = [])
    {
        if (!$database) {
            throw new InvalidArgumentException('Database name can not be empty.');
        }

        $port = null;
        if (false !== strpos($host, ':')) {
            [$host, $port] = explode(':', $host, 2);
        }

        $dsn = 'mysql:host=' . $host;
        if ($port) {
            $dsn .= ';port='. $port;
        }
        $dsn .= ';dbname=' . $database;

        // array_merge() doesnt work because it looses integer keys
        $options = $options + [
            PDO::ATTR_PERSISTENT => (bool) $persistent,
            PDO::ATTR_FETCH_TABLE_NAMES => true,
        ];

        $dbh = @new PDO($dsn, $login, $password, $options);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    /**
     * Gibt die DatenbankId der Abfrage (SQL) zurueck,
     * oder false wenn die Abfrage keine DBID enthaelt.
     *
     * @param string $qry
     *
     * @return false|int
     */
    protected static function getQueryDBID($qry)
    {
        $qry = trim($qry);

        if (preg_match('/\(DB([1-9]){1}\)/i', $qry, $matches)) {
            return (int) $matches[1];
        }

        return false;
    }

    /**
     * Entfernt die DBID aus einer Abfrage (SQL) und gibt die DBID zurueck falls
     * vorhanden, sonst false.
     *
     * @param string $qry Abfrage
     *
     * @return false|int
     */
    protected static function stripQueryDBID(&$qry)
    {
        $qry = trim($qry);

        if (false !== ($qryDBID = self::getQueryDBID($qry))) {
            $qry = substr($qry, 6);
        }

        return $qryDBID;
    }

    /**
     * Gibt den Typ der Abfrage (SQL) zurueck,
     * oder false wenn die Abfrage keinen Typ enthaelt.
     *
     * Moegliche Typen:
     * - SELECT
     * - SHOW
     * - UPDATE
     * - INSERT
     * - DELETE
     * - REPLACE
     * - CREATE
     * - CALL
     * - OPTIMIZE
     *
     * @param string $qry
     *
     * @return bool|string
     */
    public static function getQueryType($qry)
    {
        $qry = trim($qry);
        // DBID aus dem Query herausschneiden, falls vorhanden
        self::stripQueryDBID($qry);

        if (preg_match('/^(SELECT|SHOW|UPDATE|INSERT|DELETE|REPLACE|CREATE|CALL|OPTIMIZE)/i', $qry, $matches)) {
            return strtoupper($matches[1]);
        }

        return false;
    }

    /**
     * Returns a datetime string in sql datetime format (Y-m-d H:i:s) using the given timestamp or the current time
     * if no timestamp (or `null`) is given.
     *
     * @param int|null $timestamp
     *
     * @return string
     */
    public static function datetime($timestamp = null)
    {
        return date(self::FORMAT_DATETIME, null === $timestamp ? time() : $timestamp);
    }

    /**
     * Setzt eine Abfrage (SQL) ab, wechselt die DBID falls vorhanden.
     *
     * @param string $query   The sql-query
     * @param array  $params  An optional array of statement parameter
     * @param array  $options For possible option keys view `rex_sql::OPT_*` constants
     *
     * @throws rex_sql_exception on errors
     *
     * @return $this
     */
    public function setDBQuery($query, array $params = [], array $options = [])
    {
        // save origin connection-id
        $oldDBID = $this->DBID;

        try {
            // change connection-id but only for this one query
            if (false !== ($qryDBID = self::stripQueryDBID($query))) {
                $this->selectDB($qryDBID);
            }

            $this->setQuery($query, $params, $options);
        } finally {
            // restore connection-id
            $this->DBID = $oldDBID;
        }

        return $this;
    }

    /**
     * Setzt Debugmodus an/aus.
     *
     * @param bool $debug Debug TRUE/FALSE
     *
     * @return $this the current rex_sql object
     */
    public function setDebug($debug = true)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Prepares a PDOStatement.
     *
     * @param string $qry A query string with placeholders
     *
     * @throws rex_sql_exception
     *
     * @return PDOStatement The prepared statement
     */
    public function prepareQuery($qry)
    {
        $pdo = self::$pdo[$this->DBID];
        try {
            $this->query = $qry;
            $this->stmt = $pdo->prepare($qry);
            return $this->stmt;
        } catch (PDOException $e) {
            throw new rex_sql_exception('Error while preparing statement "' . $qry . '"! ' . $e->getMessage(), $e, $this);
        }
    }

    /**
     * Executes the prepared statement with the given input parameters.
     *
     * @param array $params  Array of input parameters
     * @param array $options For possible option keys view `rex_sql::OPT_*` constants
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function execute(array $params = [], array $options = [])
    {
        if (!$this->stmt) {
            throw new rex_sql_exception('you need to prepare a query before calling execute()', null, $this);
        }

        $buffered = null;
        $pdo = self::$pdo[$this->DBID];
        if (isset($options[self::OPT_BUFFERED])) {
            $buffered = $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $options[self::OPT_BUFFERED]);
        }

        try {
            $this->flush();
            $this->params = $params;

            $this->stmt->execute($params);
            $this->rows = $this->stmt->rowCount();
        } catch (PDOException $e) {
            throw new rex_sql_exception('Error while executing statement "' . $this->query . '" using params ' . json_encode($params) . '! ' . $e->getMessage(), $e, $this);
        } finally {
            if (null !== $buffered) {
                $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered);
            }

            if ($this->debug) {
                $this->printError($this->query, $params);
            }
        }

        return $this;
    }

    /**
     * Executes the given sql-query.
     *
     * If parameters will be provided, a prepared statement will be executed.
     *
     * example 1:
     *    $sql->setQuery('SELECT * FROM mytable where id=:id', ['id' => 3]);
     *
     * NOTE: named-parameters/?-placeholders are not supported in LIMIT clause!
     *
     * @param string $query   The sql-query
     * @param array  $params  An optional array of statement parameter
     * @param array  $options For possible option keys view `rex_sql::OPT_*` constants
     *
     * @throws rex_sql_exception on errors
     *
     * @return $this
     */
    public function setQuery($query, array $params = [], array $options = [])
    {
        // Alle Werte zuruecksetzen
        $this->flush();
        $this->query = $query;
        $this->params = $params;
        $this->stmt = null;

        if (!empty($params)) {
            $this->prepareQuery($query);
            $this->execute($params, $options);

            return $this;
        }

        $buffered = null;
        $pdo = self::$pdo[$this->DBID];
        if (isset($options[self::OPT_BUFFERED])) {
            $buffered = $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $options[self::OPT_BUFFERED]);
        }

        try {
            $this->stmt = rex_timer::measure(__METHOD__, static function () use ($pdo, $query) {
                return $pdo->query($query);
            });

            $this->rows = $this->stmt->rowCount();
        } catch (PDOException $e) {
            throw new rex_sql_exception('Error while executing statement "' . $query . '"! ' . $e->getMessage(), $e, $this);
        } finally {
            if (null !== $buffered) {
                $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered);
            }

            if ($this->debug) {
                $this->printError($query, $params);
            }
        }

        return $this;
    }

    /**
     * Setzt den Tabellennamen.
     *
     * @param string $table Tabellenname
     *
     * @return $this the current rex_sql object
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Sets the raw value of a column.
     *
     * @param string $colName Name of the column
     * @param string $value   The raw value
     *
     * @return $this the current rex_sql object
     */
    public function setRawValue($colName, $value)
    {
        $this->rawValues[$colName] = $value;
        unset($this->values[$colName]);

        return $this;
    }

    /**
     * Set the value of a column.
     *
     * @param string $colName Name of the column
     * @param mixed  $value   The value
     *
     * @return $this the current rex_sql object
     */
    public function setValue($colName, $value)
    {
        $this->values[$colName] = $value;
        unset($this->rawValues[$colName]);

        return $this;
    }

    /**
     * Set the array value of a column (json encoded).
     *
     * @param string $colName Name of the column
     * @param array  $value   The value
     *
     * @return $this the current rex_sql object
     */
    public function setArrayValue($colName, array $value)
    {
        return $this->setValue($colName, json_encode($value));
    }

    /**
     * Sets the datetime value of a column.
     *
     * @param string   $colName   Name of the column
     * @param int|null $timestamp Unix timestamp (if `null` is given, the current time is used)
     *
     * @return $this the current rex_sql object
     */
    public function setDateTimeValue($colName, $timestamp)
    {
        return $this->setValue($colName, self::datetime($timestamp));
    }

    /**
     * Setzt ein Array von Werten zugleich.
     *
     * @param array $valueArray Ein Array von Werten
     *
     * @return $this the current rex_sql object
     */
    public function setValues(array $valueArray)
    {
        foreach ($valueArray as $name => $value) {
            $this->setValue($name, $value);
        }

        return $this;
    }

    /**
     * Returns whether values are set inside this rex_sql object.
     *
     * @return bool True if value isset and not null, otherwise False
     */
    public function hasValues()
    {
        return !empty($this->values);
    }

    /**
     * Prueft den Wert einer Spalte der aktuellen Zeile ob ein Wert enthalten ist.
     *
     * @param string $feld Spaltenname des zu pruefenden Feldes
     * @param string $prop Wert, der enthalten sein soll
     *
     * @throws rex_sql_exception
     *
     * @return bool
     */
    protected function isValueOf($feld, $prop)
    {
        if ('' == $prop) {
            return true;
        }
        return false !== strpos($this->getValue($feld), $prop);
    }

    /**
     * Adds a record for multi row/batch operations.
     *
     * This method can only be used in combination with `insert()` and `replace()`.
     *
     * Example:
     *      $sql->addRecord(function (rex_sql $record) {
     *          $record->setValue('title', 'Foo');
     *          $record->setRawValue('created', 'NOW()');
     *      });
     *
     * @param callable $callback The callback receives a new `rex_sql` instance for the new record
     *                           and must set the values of the new record on that instance (see example above)
     *
     * @return $this
     */
    public function addRecord(callable $callback)
    {
        $record = self::factory($this->DBID);

        $callback($record);

        $this->records[] = $record;

        return $this;
    }

    /**
     * Setzt die WHERE Bedienung der Abfrage.
     *
     * example 1:
     *    $sql->setWhere(['id' => 3, 'field' => '']); // results in id = 3 AND field = ''
     *    $sql->setWhere([['id' => 3, 'field' => '']]); // results in id = 3 OR field = ''
     *
     * example 2:
     *    $sql->setWhere('myid = :id OR anotherfield = :field', ['id' => 3, 'field' => '']);
     *
     * example 3 (deprecated):
     *    $sql->setWhere('myid="35" OR abc="zdf"');
     *
     * @param string|array $where
     * @param array        $whereParams
     *
     * @throws rex_sql_exception
     *
     * @return $this the current rex_sql object
     */
    public function setWhere($where, $whereParams = null)
    {
        if (is_array($where)) {
            $this->wherevar = $where;
            $this->whereParams = [];
        } elseif (is_string($where) && is_array($whereParams)) {
            $this->wherevar = 'WHERE ' . $where;
            $this->whereParams = $whereParams;
        } elseif (is_string($where)) {
            //$trace = debug_backtrace();
            //$loc = $trace[0];
            //trigger_error('you have to take care to provide escaped values for your where-string in file "'. $loc['file'] .'" on line '. $loc['line'] .'!', E_USER_WARNING);

            $this->wherevar = 'WHERE ' . $where;
            $this->whereParams = [];
        } else {
            throw new rex_sql_exception('expecting $where to be an array, "' . gettype($where) . '" given!', null, $this);
        }

        return $this;
    }

    /**
     * Returns the tuple of `where` string and `where` params.
     *
     * @psalm-return array{0: string, 1: array}
     */
    private function buildWhere(): array
    {
        if (!$this->wherevar) {
            return ['', []];
        }

        if (is_string($this->wherevar)) {
            return [$this->wherevar, $this->whereParams];
        }

        $whereParams = [];
        $where = $this->buildWhereArg($this->wherevar, $whereParams);

        return ['WHERE '.$where, $whereParams];
    }

    /**
     * Concats the given array to a sql condition using bound parameters.
     * AND/OR opartors are alternated depending on $level.
     *
     * @param int $level
     *
     * @return string
     */
    private function buildWhereArg(array $arrFields, array &$whereParams, $level = 0)
    {
        if (1 == $level % 2) {
            $op = ' OR ';
        } else {
            $op = ' AND ';
        }

        $qry = '';
        foreach ($arrFields as $fld_name => $value) {
            if (is_array($value)) {
                $arg = '(' . $this->buildWhereArg($value, $whereParams, $level + 1) . ')';
            } else {
                $paramName = $fld_name;
                for ($i = 1; array_key_exists($paramName, $whereParams) || array_key_exists($paramName, $this->values); ++$i) {
                    $paramName = $fld_name.'_'.$i;
                }

                $arg = $this->escapeIdentifier($fld_name) . ' = :' . $paramName;
                $whereParams[$paramName] = $value;
            }

            if ('' != $qry) {
                $qry .= $op;
            }
            $qry .= $arg;
        }
        return $qry;
    }

    /**
     * Returns the value of a column.
     *
     * @param string $colName Name of the column
     *
     * @throws rex_sql_exception
     *
     * @return mixed
     */
    public function getValue($colName)
    {
        if (empty($colName)) {
            throw new rex_sql_exception('parameter fieldname must not be empty!', null, $this);
        }

        // fast fail,... value already set manually?
        if (isset($this->values[$colName])) {
            return $this->values[$colName];
        }

        // check if there is an table alias defined
        // if not, try to guess the tablename
        if (false === strpos($colName, '.')) {
            $tables = $this->getTablenames();
            foreach ($tables as $table) {
                if (in_array($table . '.' . $colName, $this->rawFieldnames)) {
                    return $this->fetchValue($table . '.' . $colName);
                }
            }
        }

        return $this->fetchValue($colName);
    }

    /**
     * Returns the array value of a (json encoded) column.
     *
     * @param string $colName Name of the column
     *
     * @throws rex_sql_exception
     *
     * @return array
     */
    public function getArrayValue($colName)
    {
        return json_decode($this->getValue($colName), true);
    }

    /**
     * Returns the unix timestamp of a datetime column.
     *
     * @param string $colName Name of the column
     *
     * @throws rex_sql_exception
     *
     * @return int|null Unix timestamp or `null` if the column is `null` or not in sql datetime format
     */
    public function getDateTimeValue($colName)
    {
        $value = $this->getValue($colName);
        return $value ? strtotime($value) : null;
    }

    /**
     * @param string $feldname
     *
     * @return mixed
     */
    protected function fetchValue($feldname)
    {
        if (isset($this->values[$feldname])) {
            return $this->values[$feldname];
        }

        if (empty($this->lastRow)) {
            // no row fetched, but also no query was executed before
            if (null == $this->stmt) {
                return null;
            }
            $this->getRow(PDO::FETCH_ASSOC);
        }

        // isset() alone doesn't work here, because values may also be null
        if (is_array($this->lastRow) && (isset($this->lastRow[$feldname]) || array_key_exists($feldname, $this->lastRow))) {
            return $this->lastRow[$feldname];
        }
        trigger_error('Field "' . $feldname . '" does not exist in result!', E_USER_WARNING);
        return null;
    }

    /**
     * Gibt den Wert der aktuellen Zeile im ResultSet zurueck
     * Falls es noch keine erste Zeile (lastRow) gibt, wird der Satzzeiger
     * initialisiert. Weitere Satzwechsel mittels next().
     *
     * @param int $fetch_type
     *
     * @return mixed
     */
    public function getRow($fetch_type = PDO::FETCH_ASSOC)
    {
        if (!$this->lastRow) {
            $lastRow = $this->stmt->fetch($fetch_type);
            if (false === $lastRow) {
                throw new rex_sql_exception('unable to fetch');
            }
            $this->lastRow = $lastRow;
        }
        return $this->lastRow;
    }

    /**
     * Prueft, ob eine Spalte im Resultset vorhanden ist.
     *
     * @param string $feldname Name der Spalte
     *
     * @return bool
     */
    public function hasValue($feldname)
    {
        // fast fail,... value already set manually?
        if (isset($this->values[$feldname])) {
            return true;
        }

        if (false !== strpos($feldname, '.')) {
            $parts = explode('.', $feldname);
            return in_array($parts[0], $this->getTablenames()) && in_array($parts[1], $this->getFieldnames());
        }
        return in_array($feldname, $this->getFieldnames());
    }

    /**
     * Prueft, ob das Feld mit dem Namen $feldname Null ist.
     *
     * Falls das Feld nicht vorhanden ist,
     * wird Null zurueckgegeben, sonst True/False
     *
     * @param string $feldname
     *
     * @throws rex_sql_exception
     *
     * @return bool|null
     */
    public function isNull($feldname)
    {
        if ($this->hasValue($feldname)) {
            return null === $this->getValue($feldname);
        }

        return null;
    }

    /**
     * Gibt die Anzahl der Zeilen zurueck.
     *
     * @return null|int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Gibt die Anzahl der Felder/Spalten zurueck.
     *
     * @return int
     */
    public function getFields()
    {
        return $this->stmt ? $this->stmt->columnCount() : 0;
    }

    /**
     * Baut den SET bestandteil mit der
     * verfuegbaren values zusammen und gibt diesen zurueck.
     *
     * @see setValue
     *
     * @return string
     */
    protected function buildPreparedValues()
    {
        $qry = '';
        if (is_array($this->values)) {
            foreach ($this->values as $fld_name => $value) {
                if ('' != $qry) {
                    $qry .= ', ';
                }

                $qry .= $this->escapeIdentifier($fld_name) .' = :' . $fld_name;
            }
        }
        if (is_array($this->rawValues)) {
            foreach ($this->rawValues as $fld_name => $value) {
                if ('' != $qry) {
                    $qry .= ', ';
                }

                $qry .= $this->escapeIdentifier($fld_name) . ' = ' . $value;
            }
        }

        if ('' == trim($qry)) {
            // FIXME
            trigger_error('no values given to buildPreparedValues for update(), insert() or replace()', E_USER_WARNING);
        }

        return $qry;
    }

    /**
     * @return string
     */
    public function getWhere()
    {
        [$where] = $this->buildWhere();

        // we have an custom where criteria, so we don't need to build one automatically
        if ('' != $where) {
            return $where;
        }

        return '';
    }

    /**
     * Setzt eine Select-Anweisung auf die angegebene Tabelle
     * mit den WHERE Parametern ab.
     *
     * @param string $fields
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function select($fields = '*')
    {
        [$where, $whereParams] = $this->buildWhere();

        $this->setQuery(
            'SELECT ' . $fields . ' FROM ' . $this->escapeIdentifier($this->table) . ' ' . $where,
            $whereParams
        );
        return $this;
    }

    /**
     * Setzt eine Update-Anweisung auf die angegebene Tabelle
     * mit den angegebenen Werten und WHERE Parametern ab.
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function update()
    {
        [$where, $whereParams] = $this->buildWhere();

        $this->setQuery(
            'UPDATE ' . $this->escapeIdentifier($this->table) . ' SET ' . $this->buildPreparedValues() . ' ' . $where,
            array_merge($this->values, $whereParams)
        );
        return $this;
    }

    /**
     * Setzt eine Insert-Anweisung auf die angegebene Tabelle
     * mit den angegebenen Werten ab.
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function insert()
    {
        if ($this->records) {
            return $this->setMultiRecordQuery('INSERT');
        }

        // hold a copies of the query fields for later debug out (the class property will be reverted in setQuery())
        $tableName = $this->table;
        $values = $this->values;

        if ($this->values || $this->rawValues) {
            $setValues = 'SET '.$this->buildPreparedValues();
        } else {
            $setValues = 'VALUES ()';
        }

        $this->setQuery(
            'INSERT INTO ' . $this->escapeIdentifier($this->table) . ' ' . $setValues,
            $this->values
        );

        // provide debug infos, if insert is considered successfull, but no rows were inserted.
        // this happens when you violate against a NOTNULL constraint
        if (0 == $this->getRows()) {
            throw new rex_sql_exception('Error while inserting into table "' . $tableName . '" with values ' . print_r($values, true) . '! Check your null/not-null constraints!', null, $this);
        }
        return $this;
    }

    /**
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function insertOrUpdate()
    {
        if ($this->records) {
            return $this->setMultiRecordQuery('INSERT', true);
        }

        // hold a copies of the query fields for later debug out (the class property will be reverted in setQuery())
        $tableName = $this->table;
        $values = $this->values;

        $onDuplicateKeyUpdate = $this->buildOnDuplicateKeyUpdate(array_keys(array_merge($this->values, $this->rawValues)));
        $this->setQuery(
            'INSERT INTO ' . $this->escapeIdentifier($this->table) . ' SET ' . $this->buildPreparedValues() . ' ' . $onDuplicateKeyUpdate,
            $this->values
        );

        return $this;
    }

    /**
     * Setzt eine Replace-Anweisung auf die angegebene Tabelle
     * mit den angegebenen Werten ab.
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function replace()
    {
        if ($this->records) {
            return $this->setMultiRecordQuery('REPLACE');
        }

        [$where, $whereParams] = $this->buildWhere();

        $this->setQuery(
            'REPLACE INTO ' . $this->escapeIdentifier($this->table) . ' SET ' . $this->buildPreparedValues() . ' ' . $where,
            array_merge($this->values, $whereParams)
        );
        return $this;
    }

    /**
     * Setzt eine Delete-Anweisung auf die angegebene Tabelle
     * mit den angegebenen WHERE Parametern ab.
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    public function delete()
    {
        [$where, $whereParams] = $this->buildWhere();

        $this->setQuery(
            'DELETE FROM ' . $this->escapeIdentifier($this->table) . ' ' . $where,
            $whereParams
        );
        return $this;
    }

    /**
     * Stellt alle Werte auf den Ursprungszustand zurueck.
     *
     * @return $this the current rex_sql object
     */
    private function flush()
    {
        $this->values = [];
        $this->rawValues = [];
        $this->records = [];
        $this->whereParams = [];
        $this->lastRow = [];
        $this->fieldnames = null;
        $this->rawFieldnames = null;
        $this->tablenames = null;

        $this->table = '';
        $this->wherevar = '';
        $this->counter = 0;
        $this->rows = 0;

        return $this;
    }

    /**
     * Stellt alle Values, die mit setValue() gesetzt wurden, zurueck.
     *
     * @see setValue(), #getValue()
     *
     * @return $this the current rex_sql object
     */
    public function flushValues()
    {
        $this->values = [];
        $this->rawValues = [];

        return $this;
    }

    /**
     * Prueft ob das Resultset weitere Datensaetze enthaelt.
     *
     * @return bool
     */
    public function hasNext()
    {
        return $this->counter < $this->rows;
    }

    /**
     * Setzt den Cursor des Resultsets zurueck zum Anfang.
     *
     * @throws rex_sql_exception
     *
     * @return $this the current rex_sql object
     */
    public function reset()
    {
        // re-execute the statement
        if ($this->stmt && 0 != $this->counter) {
            $this->execute($this->params);
            $this->counter = 0;
        }

        return $this;
    }

    /**
     * Gibt die letzte InsertId zurueck.
     *
     * @return string
     */
    public function getLastId()
    {
        return self::$pdo[$this->DBID]->lastInsertId();
    }

    /**
     * Laedt das komplette Resultset in ein Array und gibt dieses zurueck und
     * wechselt die DBID falls vorhanden.
     *
     * @param string $query     The sql-query
     * @param array  $params    An optional array of statement parameter
     * @param int    $fetchType
     *
     * @throws rex_sql_exception on errors
     *
     * @return array
     */
    public function getDBArray($query = null, array $params = [], $fetchType = PDO::FETCH_ASSOC)
    {
        if (!$query) {
            $query = $this->query;
            $params = $this->params;
        }

        $pdo = self::$pdo[$this->DBID];

        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        $this->setDBQuery($query, $params);
        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);

        return $this->stmt->fetchAll($fetchType);
    }

    /**
     * Laedt das komplette Resultset in ein Array und gibt dieses zurueck.
     *
     * @param string $query     The sql-query
     * @param array  $params    An optional array of statement parameter
     * @param int    $fetchType
     *
     * @throws rex_sql_exception on errors
     *
     * @return array
     */
    public function getArray($query = null, array $params = [], $fetchType = PDO::FETCH_ASSOC)
    {
        if (!$query) {
            $query = $this->query;
            $params = $this->params;
        }

        $pdo = self::$pdo[$this->DBID];

        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        $this->setQuery($query, $params);
        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);

        return $this->stmt->fetchAll($fetchType);
    }

    /**
     * Gibt die zuletzt aufgetretene Fehlernummer zurueck.
     *
     * @return string|null
     */
    public function getErrno()
    {
        return $this->stmt ? $this->stmt->errorCode() : self::$pdo[$this->DBID]->errorCode();
    }

    /**
     * @return int
     */
    public function getMysqlErrno()
    {
        $errorInfos = $this->stmt ? $this->stmt->errorInfo() : self::$pdo[$this->DBID]->errorInfo();

        return (int) $errorInfos[1];
    }

    /**
     * Gibt den zuletzt aufgetretene Fehler zurueck.
     */
    public function getError()
    {
        $errorInfos = $this->stmt ? $this->stmt->errorInfo() : self::$pdo[$this->DBID]->errorInfo();
        // idx0   SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
        // idx1   Driver-specific error code.
        // idx2   Driver-specific error message.
        return $errorInfos[2];
    }

    /**
     * Prueft, ob ein Fehler aufgetreten ist.
     *
     * @return bool
     */
    public function hasError()
    {
        return 0 != $this->getErrno();
    }

    /**
     * Gibt die letzte Fehlermeldung aus.
     *
     * @param string $qry
     * @param array  $params
     */
    protected function printError($qry, $params)
    {
        $errors = [];
        $errors['query'] = $qry;
        if (!empty($params)) {
            $errors['params'] = $params;

            // taken from https://github.com/doctrine/DoctrineBundle/blob/d57c1a35cd32e6b942fdda90ae3888cc1bb41e6b/Twig/DoctrineExtension.php#L290-L305
            $i = 0;
            $errors['fullquery'] = preg_replace_callback(
                '/\?|((?<!:):[a-z0-9_]+)/i',
                function ($matches) use ($params, &$i) {
                    if ('?' === $matches[0]) {
                        $keys = [$i];
                    } else {
                        $keys = [$matches[0], substr($matches[0], 1)];
                    }

                    foreach ($keys as $key) {
                        if (array_key_exists($key, $params)) {
                            ++$i;
                            return $this->escape($params[$key]);
                        }
                    }

                    return $matches[0];
                },
                $qry
            );
        }
        if ($this->getRows()) {
            $errors['count'] = $this->getRows();
        }
        if ($this->getError()) {
            $errors['error'] = $this->getError();
            $errors['ecode'] = $this->getErrno();
        }
        dump($errors);
    }

    /**
     * Setzt eine Spalte auf den naechst moeglich auto_increment Wert.
     *
     * @param string $field    Name der Spalte
     * @param int    $start_id
     *
     * @throws rex_sql_exception
     *
     * @return int
     */
    public function setNewId($field, $start_id = 0)
    {
        // setNewId muss neues sql Objekt verwenden, da sonst bestehende informationen im Objekt ueberschrieben werden
        $sql = self::factory();
        $sql->setQuery('SELECT ' . $this->escapeIdentifier($field) . ' FROM ' . $this->escapeIdentifier($this->table) . ' ORDER BY ' . $this->escapeIdentifier($field) . ' DESC LIMIT 1');
        if (0 == $sql->getRows()) {
            $id = $start_id;
        } else {
            $id = $sql->getValue($field);
        }
        ++$id;
        $this->setValue($field, $id);

        return $id;
    }

    /**
     * Gibt die Spaltennamen des ResultSets zurueck.
     *
     * @return string[]
     */
    public function getFieldnames()
    {
        $this->fetchMeta();
        assert(is_array($this->fieldnames));
        return $this->fieldnames;
    }

    /**
     * @return string[]
     */
    public function getTablenames()
    {
        $this->fetchMeta();
        assert(is_array($this->tablenames));
        return $this->tablenames;
    }

    private function fetchMeta()
    {
        if (null === $this->fieldnames) {
            $this->rawFieldnames = [];
            $this->fieldnames = [];
            $this->tablenames = [];

            for ($i = 0; $i < $this->getFields(); ++$i) {
                $metadata = $this->stmt->getColumnMeta($i);

                // strip table-name from column
                $this->fieldnames[] = substr($metadata['name'], strlen($metadata['table'] . '.'));
                $this->rawFieldnames[] = $metadata['name'];

                if (!in_array($metadata['table'], $this->tablenames)) {
                    $this->tablenames[] = $metadata['table'];
                }
            }
        }
    }

    /**
     * Escaped den uebergeben Wert fuer den DB Query.
     *
     * @param string $value den zu escapenden Wert
     *
     * @return string
     */
    public function escape($value)
    {
        return self::$pdo[$this->DBID]->quote($value);
    }

    /**
     * Escapes and adds backsticks around.
     *
     * @param string $name
     *
     * @return string
     */
    public function escapeIdentifier($name)
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    /**
     * Escapes the `LIKE` wildcard chars "%" and "_" in given value.
     */
    public function escapeLikeWildcards(string $value): string
    {
        return str_replace(['_', '%'], ['\_', '\%'], $value);
    }

    /**
     * @param string $user the name of the user who created the dataset. Defaults to the current user
     *
     * @return $this the current rex_sql object
     */
    public function addGlobalUpdateFields($user = null)
    {
        if (!$user) {
            if (rex::getUser()) {
                $user = rex::getUser()->getValue('login');
            } else {
                $user = rex::getEnvironment();
            }
        }

        $this->setDateTimeValue('updatedate', time());
        $this->setValue('updateuser', $user);

        return $this;
    }

    /**
     * @param string $user the name of the user who updated the dataset. Defaults to the current user
     *
     * @return $this the current rex_sql object
     */
    public function addGlobalCreateFields($user = null)
    {
        if (!$user) {
            if (rex::getUser()) {
                $user = rex::getUser()->getValue('login');
            } else {
                $user = rex::getEnvironment();
            }
        }

        $this->setDateTimeValue('createdate', time());
        $this->setValue('createuser', $user);

        return $this;
    }

    /**
     * Starts a database transaction.
     *
     * @throws rex_sql_exception when a transaction is already running
     *
     * @return bool Indicating whether the transaction was successfully started
     */
    public function beginTransaction()
    {
        if (self::$pdo[$this->DBID]->inTransaction()) {
            throw new rex_sql_exception('Transaction already started', null, $this);
        }
        return self::$pdo[$this->DBID]->beginTransaction();
    }

    /**
     * Rollback a already started database transaction.
     *
     * @throws rex_sql_exception when no transaction was started beforehand
     *
     * @return bool Indicating whether the transaction was successfully rollbacked
     */
    public function rollBack()
    {
        if (!self::$pdo[$this->DBID]->inTransaction()) {
            throw new rex_sql_exception('Unable to rollback, no transaction started before', null, $this);
        }
        return self::$pdo[$this->DBID]->rollBack();
    }

    /**
     * Commit a already started database transaction.
     *
     * @throws rex_sql_exception when no transaction was started beforehand
     *
     * @return bool Indicating whether the transaction was successfully committed
     */
    public function commit()
    {
        if (!self::$pdo[$this->DBID]->inTransaction()) {
            throw new rex_sql_exception('Unable to commit, no transaction started before', null, $this);
        }
        return self::$pdo[$this->DBID]->commit();
    }

    /**
     * @return bool whether a transaction was already started/is already running
     */
    public function inTransaction()
    {
        return self::$pdo[$this->DBID]->inTransaction();
    }

    /**
     * Convenience method which executes the given callable within a transaction.
     *
     * In case the callable throws, the transaction will automatically rolled back.
     * In case no error happens, the transaction will be committed after the callable was called.
     *
     * @throws Throwable
     *
     * @return mixed
     */
    public function transactional(callable $callable)
    {
        $inTransaction = self::$pdo[$this->DBID]->inTransaction();
        if (!$inTransaction) {
            self::$pdo[$this->DBID]->beginTransaction();
        }
        try {
            $result = $callable();
            if (!$inTransaction) {
                self::$pdo[$this->DBID]->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if (!$inTransaction) {
                self::$pdo[$this->DBID]->rollBack();
            }
            throw $e;
        }
    }

    // ----------------- iterator interface

    /**
     * @see http://www.php.net/manual/en/iterator.rewind.php
     *
     * @throws rex_sql_exception
     */
    public function rewind()
    {
        $this->reset();
    }

    /**
     * @see http://www.php.net/manual/en/iterator.current.php
     *
     * @return $this
     */
    public function current()
    {
        return $this;
    }

    /**
     * @see http://www.php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return $this->counter;
    }

    /**
     * @see http://www.php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        ++$this->counter;
        $this->lastRow = null;
    }

    /**
     * @see http://www.php.net/manual/en/iterator.valid.php
     */
    public function valid()
    {
        return $this->hasNext();
    }

    // ----------------- /iterator interface

    /**
     * Erstellt das CREATE TABLE Statement um die Tabelle $table
     * der Datenbankverbindung $DBID zu erstellen.
     *
     * @param string $table Name der Tabelle
     * @param int    $DBID  Id der Datenbankverbindung
     *
     * @throws rex_sql_exception
     *
     * @return string CREATE TABLE Sql-Statement zu erstsellung der Tabelle
     */
    public static function showCreateTable($table, $DBID = 1)
    {
        $sql = self::factory($DBID);
        $sql->setQuery('SHOW CREATE TABLE ' . $sql->escapeIdentifier($table));

        if (!$sql->getRows()) {
            throw new rex_sql_exception(sprintf('Table "%s" does not exist.', $table));
        }
        if (!$sql->hasValue('Create Table')) {
            throw new rex_sql_exception(sprintf('Table "%s" does not exist, it is a view instead.', $table));
        }

        return $sql->getValue('Create Table');
    }

    /**
     * Sucht alle Tabellen/Views der Datenbankverbindung $DBID.
     * Falls $tablePrefix gesetzt ist, werden nur dem Prefix entsprechende Tabellen gesucht.
     *
     * @param int         $DBID        Id der Datenbankverbindung
     * @param null|string $tablePrefix Zu suchender Tabellennamen-Prefix
     *
     * @throws rex_sql_exception
     *
     * @return array Ein Array von Tabellennamen
     *
     * @deprecated since 5.6.2, use non-static getTablesAndViews instead.
     */
    public static function showTables($DBID = 1, $tablePrefix = null)
    {
        return self::factory($DBID)->getTablesAndViews($tablePrefix);
    }

    /**
     * Sucht alle Tabellen/Views der Datenbankverbindung $DBID.
     * Falls $tablePrefix gesetzt ist, werden nur dem Prefix entsprechende Tabellen gesucht.
     *
     * @param null|string $tablePrefix Zu suchender Tabellennamen-Prefix
     *
     * @throws rex_sql_exception
     *
     * @return array Ein Array von Tabellennamen
     */
    public function getTablesAndViews($tablePrefix = null)
    {
        return $this->fetchTablesAndViews($tablePrefix);
    }

    /**
     * Sucht alle Tabellen der Datenbankverbindung $DBID.
     * Falls $tablePrefix gesetzt ist, werden nur dem Prefix entsprechende Tabellen gesucht.
     *
     * @param null|string $tablePrefix Zu suchender Tabellennamen-Prefix
     *
     * @throws rex_sql_exception
     *
     * @return array Ein Array von Tabellennamen
     */
    public function getTables($tablePrefix = null)
    {
        return $this->fetchTablesAndViews($tablePrefix, 'Table_type = "BASE TABLE"');
    }

    /**
     * Sucht alle Views der Datenbankverbindung $DBID.
     * Falls $tablePrefix gesetzt ist, werden nur dem Prefix entsprechende Views gesucht.
     *
     * @param null|string $tablePrefix Zu suchender Tabellennamen-Prefix
     *
     * @throws rex_sql_exception
     *
     * @return array Ein Array von Viewnamen
     */
    public function getViews($tablePrefix = null)
    {
        return $this->fetchTablesAndViews($tablePrefix, 'Table_type = "VIEW"');
    }

    /**
     * @param null|string $tablePrefix
     * @param null|string $where
     *
     * @throws rex_sql_exception
     *
     * @return array
     */
    private function fetchTablesAndViews($tablePrefix = null, $where = null)
    {
        $qry = 'SHOW FULL TABLES';

        $where = $where ? [$where] : [];

        if (null != $tablePrefix) {
            $column = $this->escapeIdentifier('Tables_in_'.rex::getProperty('db')[$this->DBID]['name']);
            $where[] = $column.' LIKE "' . $this->escapeLikeWildcards($tablePrefix) . '%"';
        }

        if ($where) {
            $qry .= ' WHERE '.implode(' AND ', $where);
        }

        $tables = $this->getArray($qry);
        $tables = array_map('reset', $tables);

        return $tables;
    }

    /**
     * Sucht Spalteninformationen der Tabelle $table der Datenbankverbindung $DBID.
     *
     * Beispiel fuer den Rueckgabewert:
     *
     * Array (
     *  [0] => Array (
     *    [name] => pid
     *    [type] => int(11)
     *    [null] => NO
     *    [key] => PRI
     *    [default] =>
     *    [extra] => auto_increment
     *  )
     *  [1] => Array (
     *    [name] => id
     *    [type] => int(11)
     *    [null] => NO
     *    [key] => MUL
     *    [default] =>
     *    [extra] =>
     *  )
     * )
     *
     * @param string $table Name der Tabelle
     * @param int    $DBID  Id der Datenbankverbindung
     *
     * @throws rex_sql_exception
     *
     * @return array Ein mehrdimensionales Array das die Metadaten enthaelt
     * @psalm-return list<array{name: string, type: string, null: 'YES'|'NO', key: string, default: null|string, extra: string}>
     */
    public static function showColumns($table, $DBID = 1)
    {
        $sql = self::factory($DBID);
        $sql->setQuery('SHOW COLUMNS FROM ' . $sql->escapeIdentifier($table));

        $columns = [];
        foreach ($sql as $col) {
            $columns[] = [
                'name' => (string) $col->getValue('Field'),
                'type' => (string) $col->getValue('Type'),
                'null' => (string) $col->getValue('Null'),
                'key' => (string) $col->getValue('Key'),
                'default' => null === $col->getValue('Default') ? null : (string) $col->getValue('Default'),
                'extra' => (string) $col->getValue('Extra'),
            ];
        }

        return $columns;
    }

    /**
     * Returns the full database version string.
     *
     * @param int $DBID
     *
     * @return string E.g. "5.7.7" or "5.5.5-10.4.9-MariaDB"
     */
    public static function getServerVersion($DBID = 1)
    {
        if (!isset(self::$pdo[$DBID])) {
            // create connection if necessary
            self::factory($DBID);
        }
        return self::$pdo[$DBID]->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Returns the database type (MySQL or MariaDB).
     *
     * @return string `rex_sql::MYSQL` or `rex_sql::MARIADB`
     * @psalm-return self::MYSQL|self::MARIADB
     */
    public function getDbType(): string
    {
        $version = self::$pdo[$this->DBID]->getAttribute(PDO::ATTR_SERVER_VERSION);

        return false === stripos($version, 'mariadb') ? self::MYSQL : self::MARIADB;
    }

    /**
     * Returns the normalized database version.
     *
     * @return string E.g. "5.7.7" or "10.4.9"
     */
    public function getDbVersion(): string
    {
        $version = self::$pdo[$this->DBID]->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (preg_match('/^(\d+\.\d+\.\d+)(?:-(\d+\.\d+\.\d+)-mariadb)?/i', $version, $match)) {
            return $match[2] ?? $match[1];
        }

        return $version;
    }

    /**
     * Creates a rex_sql instance.
     *
     * @param int $DBID
     *
     * @return static Returns a rex_sql instance
     */
    public static function factory($DBID = 1)
    {
        $class = static::getFactoryClass();
        return new $class($DBID);
    }

    /**
     * Prueft die uebergebenen Zugangsdaten auf gueltigkeit und legt ggf. die
     * Datenbank an.
     *
     * @param string $host
     * @param string $login
     * @param string $pw
     * @param string $dbname
     * @param bool   $createDb
     *
     * @return true|string
     */
    public static function checkDbConnection($host, $login, $pw, $dbname, $createDb = false)
    {
        if (!$dbname) {
            return rex_i18n::msg('sql_database_name_missing');
        }

        $err_msg = true;

        try {
            self::createConnection(
                $host,
                $dbname,
                $login,
                $pw
            );

            // db connection was successfully established, but we were meant to create the db
            if ($createDb) {
                // -> throw db already exists error
                $err_msg = rex_i18n::msg('sql_database_already_exists');
            }
        } catch (PDOException $e) {
            // see mysql error codes at http://dev.mysql.com/doc/refman/5.1/de/error-messages-server.html

            // ER_BAD_HOST
            if (false !== strpos($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                // unable to connect to db server
                $err_msg = rex_i18n::msg('sql_unable_to_connect_database');
            }
            // ER_BAD_DB_ERROR
            elseif (false !== strpos($e->getMessage(), 'SQLSTATE[HY000] [1049]') ||
                    false !== strpos($e->getMessage(), 'SQLSTATE[42000]')
            ) {
                if ($createDb) {
                    try {
                        // use the "mysql" db for the connection
                        $conn = self::createConnection(
                            $host,
                            'mysql',
                            $login,
                            $pw
                        );
                        if (1 !== $conn->exec('CREATE DATABASE ' . $dbname . ' CHARACTER SET utf8 COLLATE utf8_general_ci')) {
                            // unable to create db
                            $err_msg = rex_i18n::msg('sql_unable_to_create_database');
                        }
                    } catch (PDOException $e) {
                        // unable to find database
                        $err_msg = rex_i18n::msg('sql_unable_to_open_database');
                    }
                } else {
                    // unable to find database
                    $err_msg = rex_i18n::msg('sql_unable_to_find_database');
                }
            }
            // ER_ACCESS_DENIED_ERROR
            // ER_DBACCESS_DENIED_ERROR
            elseif (
                false !== strpos($e->getMessage(), 'SQLSTATE[HY000] [1045]') ||
                false !== strpos($e->getMessage(), 'SQLSTATE[28000]') ||
                false !== strpos($e->getMessage(), 'SQLSTATE[HY000] [1044]') ||
                false !== strpos($e->getMessage(), 'SQLSTATE[42000]')
            ) {
                // unable to connect to db
                $err_msg = rex_i18n::msg('sql_unable_to_connect_database');
            }
            // ER_ACCESS_TO_SERVER_ERROR
            elseif (
                false !== strpos($e->getMessage(), 'SQLSTATE[HY000] [2005]')
            ) {
                // unable to connect to server
                $err_msg = rex_i18n::msg('sql_unable_to_connect_server');
            } else {
                // we didn't expected this error, so rethrow it to show it to the admin/end-user
                throw $e;
            }
        }

        // close the connection
        $conn = null;

        return  $err_msg;
    }

    /**
     * @param string $verb
     * @param bool   $onDuplicateKeyUpdate
     *
     * @throws rex_sql_exception
     *
     * @return $this
     */
    private function setMultiRecordQuery($verb, $onDuplicateKeyUpdate = false)
    {
        $fields = [];

        foreach ($this->records as $record) {
            foreach ($record->values as $field => $value) {
                $fields[$field] = true;
            }
            foreach ($record->rawValues as $field => $value) {
                $fields[$field] = true;
            }
        }

        $fields = array_keys($fields);

        $rows = [];
        $params = [];

        foreach ($this->records as $record) {
            $row = [];

            foreach ($fields as $field) {
                if (isset($record->rawValues[$field])) {
                    $row[] = $record->rawValues[$field];

                    continue;
                }

                if (!isset($record->values[$field]) && !array_key_exists($field, $this->values)) {
                    $row[] = 'DEFAULT';

                    continue;
                }

                $row[] = '?';
                $params[] = $record->values[$field];
            }

            $rows[] = '('.implode(', ', $row).')';
        }

        $query = $verb.' INTO '.$this->escapeIdentifier($this->table)."\n";
        $query .= '('.implode(', ', array_map([$this, 'escapeIdentifier'], $fields)).")\n";
        $query .= "VALUES\n";
        $query .= implode(",\n", $rows);

        if ($onDuplicateKeyUpdate) {
            $query .= "\n".$this->buildOnDuplicateKeyUpdate($fields);
        }

        return $this->setQuery($query, $params);
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    private function buildOnDuplicateKeyUpdate($fields)
    {
        $updates = [];

        foreach ($fields as $field) {
            $field = $this->escapeIdentifier($field);
            $updates[] = "$field = VALUES($field)";
        }

        return 'ON DUPLICATE KEY UPDATE '.implode(', ', $updates);
    }
}
