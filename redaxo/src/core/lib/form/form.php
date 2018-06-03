<?php

/**
 * Klasse zum erstellen von Listen.
 *
 * @package redaxo5
 */

define('REX_FORM_ERROR_VIOLATE_UNIQUE_KEY', 1062);

/**
 * rex_form repraesentiert ein Formular in REDAXO.
 * Diese Klasse kann in Frontend u. Backend eingesetzt werden.
 *
 * Nach erzeugen eines Formulars mit der factory()-Methode muss dieses mit verschiedenen Input-Feldern bestueckt werden.
 * Dies geschieht Mittels der add*Field(...) Methoden.
 *
 * Nachdem alle Felder eingefuegt wurden, muss das Fomular mit get() oder show() ausgegeben werden.
 *
 * @package redaxo\core
 */
class rex_form extends rex_form_base
{
    use rex_factory_trait;

    protected $tableName;
    protected $whereCondition;
    protected $mode;
    protected $sql;
    protected $languageSupport;

    /**
     * Diese Konstruktor sollte nicht verwendet werden. Instanzen muessen ueber die facotry() Methode erstellt werden!
     */
    protected function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
    {
        $name = md5($tableName . $whereCondition . $method);

        parent::__construct($fieldset, $name, $method, $debug);

        $this->tableName = $tableName;
        $this->whereCondition = $whereCondition;
        $this->languageSupport = [];

        $this->sql = rex_sql::factory();
        $this->sql->setDebug($this->debug);
        $this->sql->setQuery('SELECT * FROM ' . $tableName . ' WHERE ' . $this->whereCondition . ' LIMIT 2');

        $this->setFormId('rex-addon-editmode');

        // --------- validate where-condition and determine editMode
        $numRows = $this->sql->getRows();
        if ($numRows == 0) {
            // Kein Datensatz gefunden => Mode: Add
            $this->setEditMode(false);
        } elseif ($numRows == 1) {
            // Ein Datensatz gefunden => Mode: Edit
            $this->setEditMode(true);
        } else {
            throw new rex_exception('rex_form: Die gegebene Where-Bedingung führt nicht zu einem eindeutigen Datensatz!');
        }

        // --------- Load Env
        if (rex::isBackend()) {
            $this->loadBackendConfig();
        }
    }

    /**
     * Methode zum erstellen von rex_form Instanzen.
     *
     * @param string $tableName
     * @param string $fieldset
     * @param string $whereCondition
     * @param string $method
     * @param bool   $debug
     *
     * @return static a rex_form instance
     */
    public static function factory($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
    {
        $class = static::getFactoryClass();
        return new $class($tableName, $fieldset, $whereCondition, $method, $debug);
    }

    /**
     * Laedt die Konfiguration die noetig ist um rex_form im REDAXO Backend zu verwenden.
     */
    protected function loadBackendConfig()
    {
        parent::loadBackendConfig();

        $func = rex_request('func', 'string');

        $this->addParam('func', $func);
        $this->addParam('list', rex_request('list', 'string'));

        $controlFields = [];
        $controlFields['save'] = rex_i18n::msg('form_save');
        $controlFields['apply'] = $func == 'edit' ? rex_i18n::msg('form_apply') : '';
        $controlFields['delete'] = $func == 'edit' ? rex_i18n::msg('form_delete') : '';
        $controlFields['reset'] = ''; //rex_i18n::msg('form_reset');
        $controlFields['abort'] = rex_i18n::msg('form_abort');

        // ----- EXTENSION POINT
        $controlFields = rex_extension::registerPoint(new rex_extension_point('REX_FORM_CONTROL_FIELDS', $controlFields, ['form' => $this]));

        $controlElements = [];
        foreach ($controlFields as $name => $label) {
            if ($label) {
                $attr = ['type' => 'submit', 'internal::useArraySyntax' => false, 'internal::fieldSeparateEnding' => true];

                if ($name === 'abort' || $name === 'delete') {
                    $attr['formnovalidate'] = 'formnovalidate';
                }
                $controlElements[$name] = $this->addField(
                    'button',
                    $name,
                    $label,
                    $attr,
                    false
                );
            } else {
                $controlElements[$name] = null;
            }
        }

        $this->addControlField(
            $controlElements['save'],
            $controlElements['apply'],
            $controlElements['delete'],
            $controlElements['reset'],
            $controlElements['abort']
        );
    }

    /**
     * Fuegt dem Formular ein Feld hinzu mitdem die Prioritaet von Datensaetzen verwaltet werden kann.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return rex_form_prio_element
     */
    public function addPrioField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_form_prio_element';
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Gibt die Where-Bedingung des Formulars zurueck.
     */
    public function getWhereCondition()
    {
        return $this->whereCondition;
    }

    /**
     * Mehrsprachigkeit unterstuetzen.
     *
     * @param string $idField
     * @param string $clangField
     */
    public function setLanguageSupport($idField, $clangField)
    {
        $this->languageSupport['id'] = $idField;
        $this->languageSupport['clang'] = $clangField;
    }

    /**
     * Wechselt den Modus des Formulars.
     */
    public function setEditMode($isEditMode)
    {
        if ($isEditMode) {
            $this->mode = 'edit';
        } else {
            $this->mode = 'add';
        }
    }

    /**
     * Prueft ob sich das Formular im Edit-Modus befindet.
     *
     * @return bool
     */
    public function isEditMode()
    {
        return $this->mode == 'edit';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return rex_sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    protected function getId($name)
    {
        return $this->tableName . '_' . $this->fieldset . '_' . $name;
    }

    protected function getValue($name)
    {
        if ($this->sql->getRows() == 1 && $this->sql->hasValue($name)) {
            return $this->sql->getValue($name);
        }

        return null;
    }

    /**
     * Callbackfunktion, damit in subklassen der Value noch beeinflusst werden kann
     * kurz vorm speichern.
     */
    protected function preSave($fieldsetName, $fieldName, $fieldValue, rex_sql $saveSql)
    {
        static $setOnce = false;

        if (!$setOnce) {
            $fieldnames = $this->sql->getFieldnames();

            if (in_array('updateuser', $fieldnames)) {
                $saveSql->setValue('updateuser', rex::getUser()->getValue('login'));
            }

            if (in_array('updatedate', $fieldnames)) {
                $saveSql->setDateTimeValue('updatedate', time());
            }

            if (!$this->isEditMode()) {
                if (in_array('createuser', $fieldnames)) {
                    $saveSql->setValue('createuser', rex::getUser()->getValue('login'));
                }

                if (in_array('createdate', $fieldnames)) {
                    $saveSql->setDateTimeValue('createdate', time());
                }
            }
            $setOnce = true;
        }

        return $fieldValue;
    }

    /**
     * @param object $form
     *
     * @return bool
     */
    public function equals($form)
    {
        return
            $form instanceof self &&
            $this->getTableName() == $form->getTableName() &&
            $this->getWhereCondition() == $form->getWhereCondition();
    }

    /**
     * Speichert das Formular.
     *
     * Übernimmt die Werte aus den FormElementen in die Datenbank.
     *
     * Gibt true zurück wenn alles ok war, false bei einem allgemeinen Fehler,
     * einen String mit einer Fehlermeldung oder den von der Datenbank gelieferten ErrorCode.
     *
     * @return bool
     */
    protected function save()
    {
        $sql = rex_sql::factory();
        $sql->setDebug($this->debug);
        $sql->setTable($this->tableName);

        $values = [];
        foreach ($this->getSaveElements() as $fieldsetName => $fieldsetElements) {
            foreach ($fieldsetElements as $element) {
                // read-only-fields nicht speichern
                if (strpos($element->getAttribute('class'), 'form-control-static') !== false) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $element->getSaveValue();

                // Callback, um die Values vor dem Speichern noch beeinflussen zu können
                $fieldValue = $this->preSave($fieldsetName, $fieldName, $fieldValue, $sql);

                if (is_string($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }
                $values[$fieldName] = $fieldValue;
            }
        }

        try {
            if ($this->isEditMode()) {
                $sql->setValues($values);
                $sql->setWhere($this->whereCondition);
                $sql->update();
            } else {
                if (count($this->languageSupport)) {
                    foreach (rex_clang::getAllIds() as $clang_id) {
                        $sql->setTable($this->tableName);
                        $sql->addGlobalCreateFields();
                        $sql->addGlobalUpdateFields();
                        if (!isset($id)) {
                            $id = $sql->setNewId($this->languageSupport['id']);
                        } else {
                            $sql->setValue($this->languageSupport['id'], $id);
                        }
                        $sql->setValue($this->languageSupport['clang'], $clang_id);
                        $sql->setValues($values);
                        $sql->insert();
                    }
                } else {
                    $sql->setValues($values);
                    $sql->insert();
                }
            }
            $saved = true;
        } catch (rex_sql_exception $e) {
            $saved = false;
        }

        // ----- EXTENSION POINT
        if ($saved) {
            $saved = rex_extension::registerPoint(new rex_extension_point('REX_FORM_SAVED', $saved, ['form' => $this, 'sql' => $sql]));
        } else {
            $saved = $sql->getMysqlErrno();
        }

        return $saved;
    }

    /**
     * @return bool
     */
    protected function delete()
    {
        $deleteSql = rex_sql::factory();
        $deleteSql->setDebug($this->debug);
        $deleteSql->setTable($this->tableName);
        $deleteSql->setWhere($this->whereCondition);

        try {
            $deleteSql->delete();
            $deleted = true;
        } catch (rex_sql_exception $e) {
            $deleted = false;
        }

        // ----- EXTENSION POINT
        if ($deleted) {
            $deleted = rex_extension::registerPoint(new rex_extension_point('REX_FORM_DELETED', $deleted, ['form' => $this, 'sql' => $deleteSql]));
        } else {
            $deleted = $deleteSql->getMysqlErrno();
        }

        return $deleted;
    }
}
