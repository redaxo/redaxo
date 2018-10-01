<?php

/**
 * @package redaxo\core
 */
abstract class rex_form_base
{
    /** @var string */
    protected $name;

    /** @var string "get" or "post" */
    protected $method;

    /** @var string */
    protected $fieldset;

    /** @var array */
    protected $elements;

    /** @var array */
    protected $params;

    /** @var bool */
    protected $debug;

    /** @var null|string */
    protected $applyUrl;

    /** @var null|string */
    protected $message;

    /** @var array */
    protected $errorMessages;

    /** @var string */
    protected $warning;

    /** @var null|string */
    protected $formId;

    /** @var rex_csrf_token */
    private $csrfToken;

    /**
     * Diese Konstruktor sollte nicht verwendet werden. Instanzen muessen ueber die facotry() Methode erstellt werden!
     */
    protected function __construct($fieldset, $name, $method = 'post', $debug = false)
    {
        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException("rex_form: Method-Parameter darf nur die Werte 'post' oder 'get' annehmen!");
        }

        $this->name = $name;
        $this->method = $method;
        $this->elements = [];
        $this->params = [];
        $this->addFieldset($fieldset ?: $this->name);
        $this->setMessage('');

        $this->debug = $debug;

        $this->csrfToken = rex_csrf_token::factory('rex_form_'.$this->getName());
    }

    /**
     * Initialisiert das Formular.
     */
    public function init()
    {
        // nichts tun
    }

    /**
     * Laedt die Konfiguration die noetig ist um rex_form im REDAXO Backend zu verwenden.
     */
    protected function loadBackendConfig()
    {
        $this->addParam('page', rex_be_controller::getCurrentPage());
    }

    public function setFormId($id)
    {
        $this->formId = $id;
    }

    /**
     * Gibt eine Formular-Url zurück.
     *
     * @param array $params
     * @param bool  $escape
     *
     * @return string
     */
    public function getUrl(array $params = [], $escape = true)
    {
        $params = array_merge($this->getParams(), $params);
        $params['form'] = $this->getName();

        $url = rex::isBackend() ? rex_url::backendController($params, $escape) : rex_url::frontendController($params, $escape);

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
     * Fuegt dem Formular ein Input-Feld hinzu.
     *
     * @param string $tag
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     * @param bool   $addElement
     *
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
     *
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
     *
     * @return rex_form_element
     */
    public function addInputField($type, $name, $value = null, array $attributes = [], $addElement = true)
    {
        $attributes['type'] = $type;
        $field = $this->addField('input', $name, $value, $attributes, $addElement);
        return $field;
    }

    /**
     * Fuegt dem Formular ein Text-Feld hinzu.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return rex_form_element
     */
    public function addTextField($name, $value = null, array $attributes = [])
    {
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }
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
     *
     * @return rex_form_element
     */
    public function addReadOnlyTextField($name, $value = null, array $attributes = [])
    {
        $attributes['readonly'] = 'readonly';
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }
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
     *
     * @return rex_form_element
     */
    public function addReadOnlyField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldSeparateEnding'] = true;
        $attributes['internal::noNameAttribute'] = true;
        if (!isset($attributes['class'])) {
            // Wenn die class geaendert wird, muss auch
            // rex_form_container_element::getSaveValue()
            // angepasst werden
            $attributes['class'] = 'form-control-static';
        }
        $field = $this->addField('p', $name, $value, $attributes, true);
        return $field;
    }

    /**
     * Fuegt dem Fomular ein Hidden-Feld hinzu.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
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
     *
     * @return rex_form_checkbox_element
     */
    public function addCheckboxField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_form_checkbox_element';
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
     *
     * @return rex_form_radio_element
     */
    public function addRadioField($name, $value = null, array $attributes = [])
    {
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
     *
     * @return rex_form_element
     */
    public function addTextAreaField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldSeparateEnding'] = true;
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }
        /*
        if (!isset($attributes['cols'])) {
            $attributes['cols'] = 50;
        }
        */
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
     *
     * @return rex_form_select_element
     */
    public function addSelectField($name, $value = null, array $attributes = [])
    {
        $attributes['internal::fieldClass'] = 'rex_form_select_element';
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }
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
     *
     * @throws rex_exception
     *
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
     *
     * @throws rex_exception
     *
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
     *
     * @throws rex_exception
     *
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
     *
     * @throws rex_exception
     *
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
     *
     * @return rex_form_control_element
     */
    public function addControlField($saveElement = null, $applyElement = null, $deleteElement = null, $resetElement = null, $abortElement = null)
    {
        $field = $this->addElement(new rex_form_control_element($this, $saveElement, $applyElement, $deleteElement, $resetElement, $abortElement));
        return $field;
    }

    /**
     * Fuegt dem Formular beliebiges HTML zu.
     *
     * @param string $html HTML code
     *
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
     * Gibt den Wert des Parameters $name zurueck,
     * oder $default kein Parameter mit dem Namen exisitiert.
     *
     * @param string $name
     * @param mixed  $default
     *
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
     *
     * @return rex_form_element
     */
    protected function addElement(rex_form_element $element)
    {
        $this->elements[$this->fieldset][] = $element;
        return $element;
    }

    /**
     * Erstellt ein Input-Element anhand des Strings $inputType.
     *
     * @param string $inputType
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return rex_form_element
     */
    public function createInput($inputType, $name, $value = null, array $attributes = [])
    {
        $tag = self::getInputTagName($inputType);
        $className = self::getInputClassName($inputType);
        $attributes = array_merge(self::getInputAttributes($inputType), $attributes);
        $attributes['internal::fieldClass'] = $className;

        $element = $this->createElement($tag, $name, $value, $attributes);

        return $element;
    }

    /**
     * Erstellt ein Input-Element anhand von $tag.
     *
     * @param string $tag
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return rex_form_element
     */
    protected function createElement($tag, $name, $value, array $attributes = [])
    {
        $id = $this->getId($name);

        // Evtl postwerte wieder übernehmen (auch externe Werte überschreiben)
        $postValue = $this->elementPostValue($this->getFieldsetName(), $name);
        if ($postValue !== null) {
            $value = $postValue;
        }

        // Wert aus Quelle nehmen, falls keiner extern und keiner im POST angegeben
        if ($value === null) {
            $value = $this->getValue($name);
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

    protected function getId($name)
    {
        return $this->fieldset . '_' . $name;
    }

    abstract protected function getValue($name);

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
     *
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
            case 'control':
                $className = 'rex_form_control_element';
                break;
            case 'checkbox':
                $className = 'rex_form_checkbox_element';
                break;
            case 'radio':
                $className = 'rex_form_radio_element';
                break;
            case 'select':
                $className = 'rex_form_select_element';
                break;
            case 'media':
                $className = 'rex_form_widget_media_element';
                break;
            case 'medialist':
                $className = 'rex_form_widget_medialist_element';
                break;
            case 'link':
                $className = 'rex_form_widget_linkmap_element';
                break;
            case 'linklist':
                $className = 'rex_form_widget_linklist_element';
                break;
            case 'hidden':
            case 'readonly':
            case 'readonlytext':
            case 'text':
            case 'textarea':
                $className = 'rex_form_element';
                break;
            default:
                throw new rex_exception("Unexpected inputType '" . $inputType . "'!");
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
            case 'checkbox':
            case 'hidden':
            case 'radio':
            case 'readonlytext':
            case 'text':
                return 'input';
            case 'textarea':
                return $inputType;
            case 'readonly':
                return 'p';
            default:
                $inputTag = '';
        }
        return $inputTag;
    }

    /**
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
            case 'checkbox':
            case 'hidden':
            case 'radio':
                return [
                    'type' => $inputType,
                ];
            case 'select':
                return [
                    'class' => 'form-control',
                ];
            case 'text':
                return [
                    'type' => $inputType,
                    'class' => 'form-control',
                ];
            case 'textarea':
                return [
                    'internal::fieldSeparateEnding' => true,
                    'class' => 'form-control',
                    //'cols' => 50,
                    'rows' => 6,
                ];
            case 'readonly':
                return [
                    'internal::fieldSeparateEnding' => true,
                    'internal::noNameAttribute' => true,
                    'class' => 'form-control-static',
                ];
            case 'readonlytext':
                return [
                    'type' => 'text',
                    'readonly' => 'readonly',
                    'class' => 'form-control-static',
                ];
            default:
                $inputAttr = [];
        }

        return $inputAttr;
    }

    // --------- Form Methods

    /**
     * @param rex_form_element $element
     *
     * @return bool
     */
    protected function isHeaderElement(rex_form_element $element)
    {
        return $element->getTag() == 'input' && $element->getAttribute('type') == 'hidden';
    }

    /**
     * @param rex_form_element $element
     *
     * @return bool
     */
    protected function isFooterElement(rex_form_element $element)
    {
        return $this->isControlElement($element);
    }

    /**
     * @param rex_form_element $element
     *
     * @return bool
     */
    protected function isControlElement(rex_form_element $element)
    {
        return is_a($element, 'rex_form_control_element');
    }

    /**
     * @param rex_form_element $element
     *
     * @return bool
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
        $normalizedName = rex_string::normalize($fieldsetName . '[' . $elementName . ']', '_', '[]');
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
            for ($i = 0; $i < count($this->elements[$fieldsetName]); ++$i) {
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
     * Callbackfunktion, damit in subklassen der Value noch beeinflusst werden kann
     * wenn das Feld mit Datenbankwerten angezeigt wird.
     */
    protected function preView($fieldsetName, $fieldName, $fieldValue)
    {
        return $fieldValue;
    }

    /**
     * @param string $fieldsetName
     *
     * @return array
     */
    public function fieldsetPostValues($fieldsetName)
    {
        // Name normalisieren, da der gepostete Name auch zuvor normalisiert wurde
        $normalizedFieldsetName = rex_string::normalize($fieldsetName, '_', '[]');

        return rex_post($normalizedFieldsetName, 'array');
    }

    /**
     * @param string $fieldsetName
     * @param string $fieldName
     * @param mixed  $default
     *
     * @return string
     */
    public function elementPostValue($fieldsetName, $fieldName, $default = null)
    {
        $fields = $this->fieldsetPostValues($fieldsetName);

        // name attributes are normalized
        $normalizedFieldName = rex_string::normalize($fieldName, '_', '[]');

        if (isset($fields[$normalizedFieldName])) {
            return $fields[$normalizedFieldName];
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
     * @return bool
     */
    protected function validate()
    {
        $messages = [];

        if (!$this->csrfToken->isValid()) {
            $messages[] = rex_i18n::msg('csrf_token_invalid');
        }

        foreach ($this->getSaveElements() as $fieldsetName => $fieldsetElements) {
            foreach ($fieldsetElements as $element) {
                /** @var rex_form_element $element */
                // read-only-fields
                if (strpos($element->getAttribute('class'), 'form-control-static') !== false) {
                    continue;
                }

                $validator = $element->getValidator();
                if (!$validator->isValid($element->getSaveValue())) {
                    $messages[] = $validator->getMessage();
                }
            }
        }
        return empty($messages) ? true : implode('<br />', $messages);
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
                if (strpos($element->getAttribute('class'), 'form-control-static') !== false) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $this->elementPostValue($fieldsetName, $fieldName);

                $element->setValue($fieldValue);
            }
        }
    }

    /**
     * Saves the form.
     *
     * @return bool|string|int `true` on success, an error message or an error code otherwise
     */
    abstract protected function save();

    /**
     * @return bool
     */
    protected function delete()
    {
        throw new BadMethodCallException('delete() is not implemented.');
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
            $paramString .= '&' . $name . '=' . $value;
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

        rex_extension::registerPoint(new rex_extension_point('REX_FORM_GET', $this, [], true));

        if (!$this->applyUrl) {
            $this->setApplyUrl($this->getUrl(['func' => ''], false));
        }

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

        $actionParams = [];
        if ('get' == strtolower($this->method)) {
            // Parameter dem Formular hinzufügen
            foreach ($this->getParams() as $name => $value) {
                $this->addHiddenField($name, $value, ['internal::useArraySyntax' => 'none']);
            }
        } else {
            $actionParams = $this->getParams();
        }

        $s = "\n";

        $warning = $this->getWarning();
        $message = $this->getMessage();
        if ($warning != '') {
            $s .= '  ' . rex_view::error($warning) . "\n";
        } elseif ($message != '') {
            $s .= '  ' . rex_view::success($message) . "\n";
        }

        $i = 0;
        $addHeaders = true;
        $fieldsets = $this->getFieldsetElements();
        $last = count($fieldsets);

        $id = '';
        if ($this->formId) {
            $id = ' id="'.rex_escape($this->formId).'"';
        }

        $s .= '<form' . $id . ' action="' . rex_url::backendController($actionParams) . '" method="' . $this->method . '">' . "\n";
        foreach ($fieldsets as $fieldsetName => $fieldsetElements) {
            $s .= '<fieldset>' . "\n";

            if ($fieldsetName != '' && $fieldsetName != $this->name) {
                $s .= '<legend>' . rex_escape($fieldsetName) . '</legend>' . "\n";
            }

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

            $s .= '</fieldset>' . "\n";

            ++$i;
        }

        $s .= $this->csrfToken->getHiddenField() . "\n";
        $s .= '</form>' . "\n";

        return $s;
    }

    public function show()
    {
        echo $this->get();
    }
}
