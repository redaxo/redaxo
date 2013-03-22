<?php

/**
 * Klasse zum erstellen von Listen
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
class rex_form
{
    use rex_factory_trait;

    protected $name;
    protected $tableName;
    protected $method;
    protected $fieldset;
    protected $whereCondition;
    protected $elements;
    protected $params;
    protected $mode;
    protected $sql;
    protected $debug;
    protected $applyUrl;
    protected $message;
    protected $errorMessages;
    protected $warning;
    protected $divId;

    /**
     * Diese Konstruktor sollte nicht verwendet werden. Instanzen muessen ueber die facotry() Methode erstellt werden!
     */
    protected function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
    {
//    $debug = true;

        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException("rex_form: Method-Parameter darf nur die Werte 'post' oder 'get' annehmen!");
        }

        $this->name = md5($tableName . $whereCondition . $method);
        $this->method = $method;
        $this->tableName = $tableName;
        $this->elements = [];
        $this->params = [];
        $this->addFieldset($fieldset);
        $this->whereCondition = $whereCondition;
        $this->divId = 'rex-addon-editmode';
        $this->setMessage('');

        $this->sql = rex_sql::factory();
        $this->debug = & $debug;
        $this->sql->setDebug($this->debug);
        $this->sql->setQuery('SELECT * FROM ' . $tableName . ' WHERE ' . $this->whereCondition . ' LIMIT 2');

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
     * Initialisiert das Formular
     */
    public function init()
    {
        // nichts tun
    }

    /**
     * Methode zum erstellen von rex_form Instanzen
     *
     * @param string $tableName
     * @param string $fieldset
     * @param string $whereCondition
     * @param string $method
     * @param bool   $debug
     * @return rex_form a rex_form instance
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
        $func = rex_request('func', 'string');

        $this->addParam('page', rex_request('page', 'string'));
        $this->addParam('func', $func);
        $this->addParam('list', rex_request('list', 'string'));

        $controlFields = [];
        $controlFields['save'] = rex_i18n::msg('form_save');
        $controlFields['apply']  = $func == 'edit' ? rex_i18n::msg('form_apply') : '';
        $controlFields['delete'] = $func == 'edit' ? rex_i18n::msg('form_delete') : '';
        $controlFields['reset'] = ''; //rex_i18n::msg('form_reset');
        $controlFields['abort'] = rex_i18n::msg('form_abort');

        // ----- EXTENSION POINT
        $controlFields = rex_extension::registerPoint(new rex_extension_point('REX_FORM_CONTROL_FIElDS', $controlFields, ['form' => $this]));

        $controlElements = [];
        foreach ($controlFields as $name => $label) {
            if ($label) {
                $controlElements[$name] = $this->addInputField(
                    'submit',
                    $name,
                    $label,
                    ['internal::useArraySyntax' => false],
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
     * Gibt eine Formular-Url zurück
     *
     * @param array $params
     * @param bool  $escape
     * @return string
     */
    public function getUrl(array $params = [], $escape = true)
    {
        $params = array_merge($this->getParams(), $params);
        $params['form'] = $this->getName();

        $url = rex::isBackend() ? rex_url::backendController($params) : rex_url::frontendController($params);
        if (!$escape) {
            $url = htmlspecialchars_decode($url);
        }

        return $url;
    }

    // --------- Sections

    /**
     * Fuegt dem Formular ein Fieldset hinzu.
     * Dieses dient dazu ein Formular in mehrere Abschnitte zu gliedern.
     */
    public function addFieldset($fieldset)
    {
        $this->fieldset = $fieldset;
    }

    // --------- Fields

    /**
     * Fuegt dem Formular ein Input-Feld hinzu
     *
     * @param string $tag
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @param bool   $addElement
     * @return rex_form_element
     */
    public function addField($tag, $name, $value = null, array $attributes = [], $addElement = true)
    {
        $element = $this->createElement($tag, $name, $value, $attributes);

        if ($addElement) {
            $this->addElement($element);
            return $element;
        }

        return $element;
    }

    /**
     * Fuegt dem Formular ein Container-Feld hinzu.
     *
     * Ein Container-Feld wiederrum kann weitere Felder enthalten.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_container_element
     */
    public function addContainerField($name, $value = null, array $attributes = [])
    {
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'rex-form-container';
        }
        $attributes['internal::fieldClass'] = 'rex_form_container_element';

        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Input-Feld mit dem Type $type hinzu.
     *
     * @param string $type
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @param bool   $addElement
     * @return rex_form_element
     */
    public function addInputField($type, $name, $value = null, array $attributes = [], $addElement = true)
    {
        $attributes['type'] = $type;
        $field = $this->addField('input', $name, $value, $attributes, $addElement);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Text-Feld hinzu
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    public function addTextField($name, $value = null, array $attributes = [])
    {
        $field = $this->addInputField('text', $name, $value, $attributes);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Read-Only-Text-Feld hinzu.
     * Dazu wird ein input-Element verwendet.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    public function addReadOnlyTextField($name, $value = null, array $attributes = [])
    {
        $attributes['readonly'] = 'readonly';
        $field = $this->addInputField('text', $name, $value, $attributes);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Read-Only-Feld hinzu.
     * Dazu wird ein span-Element verwendet.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    public function addReadOnlyField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldSeparateEnding'] = true;
        $attributes['internal::noNameAttribute'] = true;
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'rex-form-read';
        }
        $field = $this->addField('span', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Fomular ein Hidden-Feld hinzu.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    public function addHiddenField($name, $value = null, array $attributes = [])
    {
        $field = $this->addInputField('hidden', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Fomular ein Checkbox-Feld hinzu.
     * Dies ermoeglicht die Mehrfach-Selektion aus einer vorgegeben Auswahl an Werten.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_checkbox_element
     */
    public function addCheckboxField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_form_checkbox_element';
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'rex-form-checkbox rex-form-label-right';
        }
        $field = $this->addField('', $name, $value, $attributes);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Radio-Feld hinzu.
     * Dies ermoeglicht eine Einfache-Selektion aus einer vorgegeben Auswahl an Werten.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_radio_element
     */
    public function addRadioField($name, $value = null, array $attributes = [])
    {
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'rex-form-radio';
        }
        $attributes['internal::fieldClass'] = 'rex_form_radio_element';
        $field = $this->addField('radio', $name, $value, $attributes);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Textarea-Feld hinzu.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    public function addTextAreaField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldSeparateEnding'] = true;
        if (!isset($attributes['cols'])) {
            $attributes['cols'] = 50;
        }
        if (!isset($attributes['rows'])) {
            $attributes['rows'] = 6;
        }

        $field = $this->addField('textarea', $name, $value, $attributes);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Select/Auswahl-Feld hinzu.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_select_element
     */
    public function addSelectField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_form_select_element';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Feld hinzu mitdem die Prioritaet von Datensaetzen verwaltet werden kann.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_prio_element
     */
    public function addPrioField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_form_prio_element';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Feld hinzu mit dem der Medienpool angebunden werden kann.
     * Es kann nur ein Element aus dem Medienpool eingefuegt werden.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @throws rex_exception
     * @return rex_form_widget_media_element
     */
    public function addMediaField($name, $value = null, array $attributes = [])
    {
        if (!rex_addon::get('mediapool')->isAvailable()) {
            throw new rex_exception(__METHOD__ . '() needs "mediapool" addon!');
        }
        $attributes['internal::fieldClass'] = 'rex_form_widget_media_element';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Feld hinzu mit dem der Medienpool angebunden werden kann.
     * Damit koennen mehrere Elemente aus dem Medienpool eingefuegt werden.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @throws rex_exception
     * @return rex_form_widget_medialist_element
     */
    public function addMedialistField($name, $value = null, array $attributes = [])
    {
        if (!rex_addon::get('mediapool')->isAvailable()) {
            throw new rex_exception(__METHOD__ . '() needs "mediapool" addon!');
        }
        $attributes['internal::fieldClass'] = 'rex_form_widget_medialist_element';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Feld hinzu mit dem die Struktur-Verwaltung angebunden werden kann.
     * Es kann nur ein Element aus der Struktur eingefuegt werden.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @throws rex_exception
     * @return rex_form_widget_linkmap_element
     */
    public function addLinkmapField($name, $value = null, array $attributes = [])
    {
        if (!rex_addon::get('structure')->isAvailable()) {
            throw new rex_exception(__METHOD__ . '() needs "structure" addon!');
        }
        $attributes['internal::fieldClass'] = 'rex_form_widget_linkmap_element';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Feld hinzu mit dem die Struktur-Verwaltung angebunden werden kann.
     * Damit koennen mehrere Elemente aus der Struktur eingefuegt werden.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @throws rex_exception
     * @return rex_form_widget_linklist_element
     */
    public function addLinklistField($name, $value = null, array $attributes = [])
    {
        if (!rex_addon::get('structure')->isAvailable()) {
            throw new rex_exception(__METHOD__ . '() needs "structure" addon!');
        }
        $attributes['internal::fieldClass'] = 'rex_form_widget_linklist_element';
        $field = $this->addField('', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Fomualar ein Control-Feld hinzu.
     * Damit koennen versch. Aktionen mit dem Fomular durchgefuert werden.
     *
     * @param rex_form_element $saveElement
     * @param rex_form_element $applyElement
     * @param rex_form_element $deleteElement
     * @param rex_form_element $resetElement
     * @param rex_form_element $abortElement
     * @return rex_form_control_element
     */
    public function addControlField($saveElement = null, $applyElement = null, $deleteElement = null, $resetElement = null, $abortElement = null)
    {
        $field = $this->addElement(new rex_form_control_element($this, $saveElement, $applyElement, $deleteElement, $resetElement, $abortElement));
        return $field;
    }

    /**
     * Fuegt dem Formular beliebiges HTML zu.
     * @param string $html HTML code
     * @return rex_form_raw_element
     */
    public function addRawField($html)
    {
        $field = $this->addElement(new rex_form_raw_element($html));
        return $field;
    }

    /**
     * Fuegt dem Formular eine Fehlermeldung hinzu.
     */
    public function addErrorMessage($errorCode, $errorMessage)
    {
        $this->errorMessages[$errorCode] = $errorMessage;
    }

    /**
     * Fuegt dem Formular einen Parameter hinzu.
     * Diese an den Stellen eingefuegt, an denen das Fomular neue Requests erzeugt.
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Gibt alle Parameter des Fomulars zurueck.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Gibt die Where-Bedingung des Formulars zurueck
     */
    public function getWhereCondition()
    {
        return $this->whereCondition;
    }

    /**
     * Gibt den Wert des Parameters $name zurueck,
     * oder $default kein Parameter mit dem Namen exisitiert.
     *
     * @param string $name
     * @param mixed  $default
     * @return string
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $default;
    }

    /**
     * Allgemeine Bootleneck-Methode um Elemente in das Formular einzufuegen.
     *
     * @param rex_form_element $element
     * @return rex_form_element
     */
    protected function addElement(rex_form_element $element)
    {
        $this->elements[$this->fieldset][] = $element;
        return $element;
    }

    /**
     * Erstellt ein Input-Element anhand des Strings $inputType
     *
     * @param string $inputType
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    public function createInput($inputType, $name, $value = null, array $attributes = [])
    {
        $tag        = self::getInputTagName($inputType);
        $className  = self::getInputClassName($inputType);
        $attributes = array_merge(self::getInputAttributes($inputType), $attributes);
        $attributes['internal::fieldClass'] = $className;

        $element = $this->createElement($tag, $name, $value, $attributes);

        return $element;
    }

    /**
     * Erstellt ein Input-Element anhand von $tag
     *
     * @param string $tag
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @return rex_form_element
     */
    protected function createElement($tag, $name, $value, array $attributes = [])
    {
        $id = $this->tableName . '_' . $this->fieldset . '_' . $name;

        // Evtl postwerte wieder übernehmen (auch externe Werte überschreiben)
        $postValue = $this->elementPostValue($this->getFieldsetName(), $name);
        if ($postValue !== null) {
            $value = $postValue;
        }

        // Wert aus der DB nehmen, falls keiner extern und keiner im POST angegeben
        if ($value === null && $this->sql->getRows() == 1 && $this->sql->hasValue($name)) {
            $value = $this->sql->getValue($name);
        }

        if (is_array($value)) {
            $value = '|' . implode('|', $value) . '|';
        }

        if (!isset($attributes['internal::useArraySyntax'])) {
            $attributes['internal::useArraySyntax'] = true;
        }

        // Eigentlichen Feldnamen nochmals speichern
        $fieldName = $name;
        if ($attributes['internal::useArraySyntax'] === true) {
            $name = $this->fieldset . '[' . $name . ']';
        } elseif ($attributes['internal::useArraySyntax'] === false) {
            $name = $this->fieldset . '_' . $name;
        }
        unset($attributes['internal::useArraySyntax']);

        $class = 'rex_form_element';
        if (isset($attributes['internal::fieldClass'])) {
            $class = $attributes['internal::fieldClass'];
            unset($attributes['internal::fieldClass']);
        }

        $separateEnding = false;
        if (isset($attributes['internal::fieldSeparateEnding'])) {
            $separateEnding = $attributes['internal::fieldSeparateEnding'];
            unset($attributes['internal::fieldSeparateEnding']);
        }

        $internal_attr = ['name' => $name];
        if (isset($attributes['internal::noNameAttribute'])) {
            $internal_attr = [];
            unset($attributes['internal::noNameAttribute']);
        }

        // 1. Array: Eigenschaften, die via Parameter Überschrieben werden können/dürfen
        // 2. Array: Eigenschaften, via Parameter
        // 3. Array: Eigenschaften, die hier fest definiert sind / nicht veränderbar via Parameter
        $attributes = array_merge(['id' => $id], $attributes, $internal_attr);
        $element = new $class($tag, $this, $attributes, $separateEnding);
        $element->setFieldName($fieldName);
        $element->setValue($value);
        return $element;
    }

    /**
     * Wechselt den Modus des Formulars
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
     * @return boolean
     */
    public function isEditMode()
    {
        return $this->mode == 'edit';
    }

    /**
     * Setzt die Url die bei der apply-action genutzt wird.
     */
    public function setApplyUrl($url)
    {
        if (is_array($url)) {
            $url = $this->getUrl($url, false);
        }

        $this->applyUrl = $url;
    }

    // --------- Static Methods

    /**
     * @param string $inputType
     * @throws rex_exception
     *
     * @return rex_form_element
     */
    public static function getInputClassName($inputType)
    {
        // ----- EXTENSION POINT
        $className = rex_extension::registerPoint(new rex_extension_point('REX_FORM_INPUT_CLASS', '', ['inputType' => $inputType]));

        if ($className) {
            return $className;
        }

        switch ($inputType) {
            case 'control'   : $className = 'rex_form_control_element'; break;
            case 'checkbox'  : $className = 'rex_form_checkbox_element'; break;
            case 'radio'     : $className = 'rex_form_radio_element'; break;
            case 'select'    : $className = 'rex_form_select_element'; break;
            case 'media'     : $className = 'rex_form_widget_media_element'; break;
            case 'medialist' : $className = 'rex_form_widget_medialist_element'; break;
            case 'link'      : $className = 'rex_form_widget_linkmap_element'; break;
            case 'linklist'  : $className = 'rex_form_widget_linklist_element'; break;
            case 'hidden'    :
            case 'readonly'  :
            case 'readonlytext' :
            case 'text'      :
            case 'textarea'  : $className = 'rex_form_element'; break;
            default          : throw new rex_exception("Unexpected inputType '" . $inputType . "'!");
        }
        return $className;
    }

    /**
     * @param string $inputType
     *
     * @return string
     */
    public static function getInputTagName($inputType)
    {
        // ----- EXTENSION POINT
        $inputTag = rex_extension::registerPoint(new rex_extension_point('REX_FORM_INPUT_TAG', '', ['inputType' => $inputType]));

        if ($inputTag) {
            return $inputTag;
        }

        switch ($inputType) {
            case 'checkbox'  :
            case 'hidden'    :
            case 'radio'     :
            case 'readonlytext' :
            case 'text'      : return 'input';
            case 'textarea'  : return $inputType;
            case 'readonly'  : return 'span';
            default          : $inputTag = ''; break;
        }
        return $inputTag;
    }

    /**
     *
     * @param string $inputType
     *
     * @return array
     */
    public static function getInputAttributes($inputType)
    {
        // ----- EXTENSION POINT
        $inputAttr = rex_extension::registerPoint(new rex_extension_point('REX_FORM_INPUT_ATTRIBUTES', [], ['inputType' => $inputType]));

        if ($inputAttr) {
            return $inputAttr;
        }

        switch ($inputType) {
            case 'checkbox'  :
            case 'hidden'    :
            case 'radio'     :
            case 'text'      :
                return [
                    'type' => $inputType,
                    'class' => 'rex-form-' . $inputType
                ];
            case 'textarea'  :
                return [
                    'internal::fieldSeparateEnding' => true,
                    'class' => 'rex-form-textarea',
                    'cols' => 50,
                    'rows' => 6
                ];
            case 'readonly'  :
                return [
                    'internal::fieldSeparateEnding' => true,
                    'internal::noNameAttribute' => true,
                    'class' => 'rex-form-read'
                ];
            case 'readonlytext'  :
                return [
                    'type' => 'text',
                    'readonly' => 'readonly',
                    'class' => 'rex-form-read'
                ];
            default          : $inputAttr = []; break;
        }
        return $inputAttr;
    }

    // --------- Form Methods

    /**
     * @param rex_form_element $element
     *
     * @return boolean
     */
    protected function isHeaderElement(rex_form_element $element)
    {
        return $element->getTag() == 'input' && $element->getAttribute('type') == 'hidden';
    }

    /**
     * @param rex_form_element $element
     *
     * @return boolean
     */
    protected function isFooterElement(rex_form_element $element)
    {
        return $this->isControlElement($element);
    }

    /**
     * @param rex_form_element $element
     *
     * @return boolean
     */
    protected function isControlElement(rex_form_element $element)
    {
        return is_a($element, 'rex_form_control_element');
    }


    /**
     * @param rex_form_element $element
     *
     * @return boolean
     */
    protected function isRawElement(rex_form_element $element)
    {
        return is_a($element, 'rex_form_raw_element');
    }

    /**
     * @return rex_form_element[]
     */
    protected function getHeaderElements()
    {
        $headerElements = [];
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            foreach ($fieldsetElementsArray as $element) {
                if ($this->isHeaderElement($element)) {
                    $headerElements[] = $element;
                }
            }
        }
        return $headerElements;
    }

    /**
     * @return rex_form_element[]
     */
    protected function getFooterElements()
    {
        $footerElements = [];
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            foreach ($fieldsetElementsArray as $element) {
                if ($this->isFooterElement($element)) {
                    $footerElements[] = $element;
                }
            }
        }
        return $footerElements;
    }

    /**
     * @return string
     */
    protected function getFieldsetName()
    {
        return $this->fieldset;
    }

    /**
     * @return array
     */
    protected function getFieldsets()
    {
        $fieldsets = [];
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            $fieldsets[] = $fieldsetName;
        }
        return $fieldsets;
    }

    /**
     * @return array
     */
    protected function getFieldsetElements()
    {
        $fieldsetElements = [];
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            $fieldsetElements[$fieldsetName] = [];

            foreach ($fieldsetElementsArray as $element) {
                if ($this->isHeaderElement($element)) {
                    continue;
                }
                if ($this->isFooterElement($element)) {
                    continue;
                }

                $fieldsetElements[$fieldsetName][] = $element;
            }
        }
        return $fieldsetElements;
    }

    /**
     * @return array
     */
    protected function getSaveElements()
    {
        $fieldsetElements = [];
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            $fieldsetElements[$fieldsetName] = [];

            foreach ($fieldsetElementsArray as $key => $element) {
                if ($this->isFooterElement($element)) {
                    continue;
                }
                if ($this->isRawElement($element)) {
                    continue;
                }

                $fieldsetElements[$fieldsetName][] = $element;
            }
        }
        return $fieldsetElements;
    }

    /**
     * @return rex_form_control_element
     */
    protected function getControlElement()
    {
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            foreach ($fieldsetElementsArray as $element) {
                if ($this->isControlElement($element)) {
                    return $element;
                }
            }
        }
        $noElement = null;
        return $noElement;
    }

    /**
     * @param string $fieldsetName
     * @param string $elementName
     *
     * @return rex_form_element
     */
    protected function getElement($fieldsetName, $elementName)
    {
        $normalizedName = rex_form_element::_normalizeName($fieldsetName . '[' . $elementName . ']');
        $result = $this->_getElement($fieldsetName, $normalizedName);
        return $result;
    }

    /**
     * @param string $fieldsetName
     * @param string $elementName
     *
     * @return rex_form_element
     */
    private function _getElement($fieldsetName, $elementName)
    {
        if (is_array($this->elements[$fieldsetName])) {
            for ($i = 0; $i < count($this->elements[$fieldsetName]); $i++) {
                if ($this->elements[$fieldsetName][$i]->getAttribute('name') == $elementName) {
                    return $this->elements[$fieldsetName][$i];
                }
            }
        }
        $result = null;
        return $result;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setWarning($warning)
    {
        $this->warning = $warning;
    }

    /**
     * @return string
     */
    public function getWarning()
    {
        $warning = rex_request($this->getName() . '_warning', 'string');
        if ($this->warning != '') {
            $warning .= "\n" . $this->warning;
        }
        return $warning;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        $message = rex_request($this->getName() . '_msg', 'string');
        if ($this->message != '') {
            $message .= "\n" . $this->message;
        }
        return $message;
    }

    /**
     * @return rex_sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Callbackfunktion, damit in subklassen der Value noch beeinflusst werden kann
     * kurz vorm speichern
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
     * Callbackfunktion, damit in subklassen der Value noch beeinflusst werden kann
     * wenn das Feld mit Datenbankwerten angezeigt wird
     */
    protected function preView($fieldsetName, $fieldName, $fieldValue)
    {
        return $fieldValue;
    }

    /**
     * @param string $fieldsetName
     * @return array
     */
    public function fieldsetPostValues($fieldsetName)
    {
        // Name normalisieren, da der gepostete Name auch zuvor normalisiert wurde
        $normalizedFieldsetName = rex_form_element::_normalizeName($fieldsetName);

        return rex_post($normalizedFieldsetName, 'array');
    }

    /**
     * @param string $fieldsetName
     * @param string $fieldName
     * @param mixed  $default
     * @return string
     */
    public function elementPostValue($fieldsetName, $fieldName, $default = null)
    {
        $fields = $this->fieldsetPostValues($fieldsetName);

        if (isset($fields[$fieldName])) {
            return $fields[$fieldName];
        }

        return $default;
    }

    /**
     * Validiert die Eingaben.
     * Gibt true zurück wenn alles ok war, false bei einem allgemeinen Fehler oder
     * einen String mit einer Fehlermeldung.
     *
     * Eingaben sind via
     *   $el    = $this->getElement($fieldSetName, $fieldName);
     *   $val   = $el->getValue();
     * erreichbar.
     *
     * @return boolean
     */
    protected function validate()
    {
        return true;
    }

    /**
     * Übernimmt die POST-Werte in die FormElemente.
     */
    protected function processPostValues()
    {
        $saveElements = $this->getSaveElements();
        foreach ($saveElements as $fieldsetName => $fieldsetElements) {
            foreach ($fieldsetElements as $key => $element) {
                // read-only-fields nicht speichern
                if (strpos($element->getAttribute('class'), 'rex-form-read') !== false) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $this->elementPostValue($fieldsetName, $fieldName);

                if (is_array($fieldValue)) {
                    $fieldValue = '|' . implode('|', $fieldValue) . '|';
                }

                $element->setValue($fieldValue);
            }
        }
    }

    /**
     * @param object $form
     *
     * @return boolean
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
     * @return boolean
     */
    protected function save()
    {
        $sql = rex_sql::factory();
        $sql->setDebug($this->debug);
        $sql->setTable($this->tableName);

        foreach ($this->getSaveElements() as $fieldsetName => $fieldsetElements) {
            foreach ($fieldsetElements as $element) {
                // read-only-fields nicht speichern
                if (strpos($element->getAttribute('class'), 'rex-form-read') !== false) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $element->getSaveValue();

                // Callback, um die Values vor dem Speichern noch beeinflussen zu können
                $fieldValue = $this->preSave($fieldsetName, $fieldName, $fieldValue, $sql);

                $sql->setValue($fieldName, $fieldValue);
            }
        }

        try {
            if ($this->isEditMode()) {
                $sql->setWhere($this->whereCondition);
                $sql->update();
            } else {
                $sql->insert();
            }
            $saved = true;
        } catch (rex_sql_exception $e) {
            $saved = false;
        }


        // ----- EXTENSION POINT
        if ($saved) {
            $saved = rex_extension::registerPoint(new rex_extension_point('REX_FORM_SAVED', $saved, ['form' => $this, 'sql' => $sql]));
        } else {
            $saved = $sql->getErrno();
        }

        return $saved;
    }

    /**
     * @return boolean
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
            $deleted = $deleteSql->getErrno();
        }

        return $deleted;
    }

    protected function redirect($listMessage = '', $listWarning = '', array $params = [])
    {
        if ($listMessage != '') {
            $listName = rex_request('list', 'string');
            $params[$listName . '_msg'] = $listMessage;
        }

        if ($listWarning != '') {
            $listName = rex_request('list', 'string');
            $params[$listName . '_warning'] = $listWarning;
        }

        $paramString = '';
        foreach ($params as $name => $value) {
            $paramString = $name . '=' . $value . '&';
        }

        if ($this->debug) {
            echo 'redirect to: ' . $this->applyUrl . $paramString;
            exit();
        }

        header('Location: ' . $this->applyUrl . $paramString);
        exit();
    }

    /**
     * @return string
     */
    public function get()
    {
        $this->init();

        $this->setApplyUrl($this->getUrl(['func' => ''], false));

        if (($controlElement = $this->getControlElement()) !== null) {
            if ($controlElement->saved()) {
                $this->processPostValues();

                // speichern und umleiten
                // Nachricht in der Liste anzeigen
                if (($result = $this->validate()) === true && ($result = $this->save()) === true) {
                    $this->redirect(rex_i18n::msg('form_saved'));
                } elseif (is_int($result) && isset($this->errorMessages[$result])) {
                    $this->setWarning($this->errorMessages[$result]);
                } elseif (is_string($result) && $result != '') {
                    $this->setWarning($result);
                } else {
                    $this->setWarning(rex_i18n::msg('form_save_error'));
                }
            } elseif ($controlElement->applied()) {
                $this->processPostValues();

                // speichern und wiederanzeigen
                // Nachricht im Formular anzeigen
                if (($result = $this->validate()) === true && ($result = $this->save()) === true) {
                    $this->setMessage(rex_i18n::msg('form_applied'));
                } elseif (is_int($result) && isset($this->errorMessages[$result])) {
                    $this->setWarning($this->errorMessages[$result]);
                } elseif (is_string($result) && $result != '') {
                    $this->setWarning($result);
                } else {
                    $this->setWarning(rex_i18n::msg('form_save_error'));
                }
            } elseif ($controlElement->deleted()) {
                // speichern und wiederanzeigen
                // Nachricht in der Liste anzeigen
                if (($result = $this->delete()) === true) {
                    $this->redirect(rex_i18n::msg('form_deleted'));
                } elseif (is_string($result) && $result != '') {
                    $this->setWarning($result);
                } else {
                    $this->setWarning(rex_i18n::msg('form_delete_error'));
                }
            } elseif ($controlElement->resetted()) {
                // verwerfen und wiederanzeigen
                // Nachricht im Formular anzeigen
                $this->setMessage(rex_i18n::msg('form_resetted'));
            } elseif ($controlElement->aborted()) {
                // verwerfen und umleiten
                // Nachricht in der Liste anzeigen
                $this->redirect(rex_i18n::msg('form_resetted'));
            }
        }

        // Parameter dem Formular hinzufügen
        foreach ($this->getParams() as $name => $value) {
            $this->addHiddenField($name, $value, ['internal::useArraySyntax' => 'none']);
        }

        $s = "\n";

        $warning = $this->getWarning();
        $message = $this->getMessage();
        if ($warning != '') {
            $s .= '  ' . rex_view::warning($warning) . "\n";
        } elseif ($message != '') {
            $s .= '  ' . rex_view::info($message) . "\n";
        }

        $s .= '<div id="' . $this->divId . '" class="rex-form">' . "\n";

        $i = 0;
        $addHeaders = true;
        $fieldsets = $this->getFieldsetElements();
        $last = count($fieldsets);

        $s .= '    <form action="' . rex_url::backendController() . '" method="' . $this->method . '">' . "\n";
        foreach ($fieldsets as $fieldsetName => $fieldsetElements) {
            $s .= '        <fieldset">' . "\n";
            $s .= '            <h2>' . htmlspecialchars($fieldsetName) . '</h2>' . "\n";

            // Die HeaderElemente nur im 1. Fieldset ganz am Anfang einfügen
            if ($i == 0 && $addHeaders) {
                foreach ($this->getHeaderElements() as $element) {
                    // Callback
                    $element->setValue($this->preView($fieldsetName, $element->getFieldName(), $element->getValue()));
                    // HeaderElemente immer ohne <p>
                    $s .= $element->formatElement();
                }
                $addHeaders = false;
            }

            foreach ($fieldsetElements as $element) {
                // Callback
                $element->setValue($this->preView($fieldsetName, $element->getFieldName(), $element->getValue()));
                $s .= $element->get();
            }

            // Die FooterElemente nur innerhalb des letzten Fieldsets
            if (($i + 1) == $last) {
                foreach ($this->getFooterElements() as $element) {
                    // Callback
                    $element->setValue($this->preView($fieldsetName, $element->getFieldName(), $element->getValue()));
                    $s .= $element->get();
                }
            }

            $s .= '        </fieldset>' . "\n";

            $i++;
        }

        $s .= '    </form>' . "\n";
        $s .= '</div>' . "\n";

        return $s;
    }

    public function show()
    {
        echo $this->get();
    }
}
