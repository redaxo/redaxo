<?php

/**
 * @package redaxo\core\form
 */
class rex_form_prio_element extends rex_form_select_element
{
    /** @var string */
    private $labelField;
    /** @var callable */
    private $labelCallback;
    /** @var string */
    private $whereCondition;
    /** @var string */
    private $primaryKey;
    /** @var string */
    private $firstOptionMsg;
    /** @var string */
    private $optionMsg;
    /** @var rex_form */
    protected $table;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag, rex_form $table, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
        $this->table = $table;

        $this->labelField = '';
        $this->whereCondition = '';
        $this->primaryKey = 'id';
        $this->firstOptionMsg = 'form_field_first_priority';
        $this->optionMsg = 'form_field_after_priority';
        $this->select->setSize(1);

        rex_extension::register('REX_FORM_SAVED', [$this, 'organizePriorities']);
        rex_extension::register('REX_FORM_DELETED', [$this, 'organizePriorities']);
    }

    /**
     * Setzt die Datenbankspalte, die das Label für die zu priorisierenden Elemente darstellt.
     *
     * @param string $labelField
     */
    public function setLabelField($labelField)
    {
        $this->labelField = $labelField;
    }

    public function setLabelCallback(callable $labelCallback)
    {
        $this->labelCallback = $labelCallback;
    }

    public function setWhereCondition($whereCondition)
    {
        $this->whereCondition = $whereCondition;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    public function formatElement()
    {
        $name = $this->getFieldName();

        $qry = 'SELECT ' . $this->labelField . ',' . $name . ' FROM ' . $this->table->getTableName() . ' WHERE 1=1';
        if ('' != $this->whereCondition) {
            $qry .= ' AND (' . $this->whereCondition . ')';
        }

        // Im Edit Mode das Feld selbst nicht als Position einfügen
        if ($this->table->isEditMode()) {
            $qry .= ' AND (' . $name . '!=' . $this->getValue() . ')';
        }

        $qry .= ' ORDER BY ' . $name;
        $sql = rex_sql::factory();
        $sql->setQuery($qry);

        $this->select->addOption(rex_i18n::msg($this->firstOptionMsg), 1);
        $value = 1;
        foreach ($sql as $opt) {
            $value = $opt->getValue($name) + 1;
            $label = $opt->getValue($this->labelField);

            if ($this->labelCallback) {
                $label = call_user_func($this->labelCallback, $label);
            }

            $this->select->addOption(rex_i18n::rawMsg($this->optionMsg, $label), $value);
        }
        if (!$this->table->isEditMode()) {
            $this->select->setSelected($value);
        }

        return parent::formatElement();
    }

    public function organizePriorities(rex_extension_point $ep)
    {
        if ($this->table->equals($ep->getParam('form'))) {
            $name = $this->getFieldName();

            rex_sql_util::organizePriorities(
                $this->table->getTableName(),
                $name,
                $this->whereCondition,
                $name . ', updatedate desc'
            );
        }
    }
}
