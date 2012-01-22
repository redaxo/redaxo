<?php

class rex_form_prio_element extends rex_form_select_element
{
  private
    $labelField,
    $whereCondition,
    $primaryKey,
    $firstOptionMsg,
    $optionMsg;

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  public function __construct($tag = '', rex_form $table = null, array $attributes = array())
  {
    parent::__construct('', $table, $attributes);

    $this->labelField = '';
    $this->whereCondition = '';
    $this->primaryKey = 'id';
    $this->firstOptionMsg = 'form_field_first_prior';
    $this->optionMsg = 'form_field_after_prior';
    $this->select->setSize(1);

    rex_extension::register('REX_FORM_SAVED', array($this, 'organizePriorities'));
  }

  /**
   * Setzt die Datenbankspalte, die das Label für die zu priorisierenden Elemente darstellt
   * @param $labelField String
   */
  public function setLabelField($labelField)
  {
    $this->labelField = $labelField;
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

    $qry = 'SELECT '. $this->labelField .','. $name .' FROM '. $this->table->getTableName() . ' WHERE 1=1';
    if($this->whereCondition != '')
    {
      $qry .= ' AND ('. $this->whereCondition .')';
    }

    // Im Edit Mode das Feld selbst nicht als Position einfügen
    if($this->table->isEditMode())
    {
      $sql = $this->table->getSql();
      $qry .= ' AND ('. $name .'!='. $this->getValue() .')';
    }

    $qry .=' ORDER BY '. $name;
    $sql = rex_sql::factory();
    $sql->setQuery($qry);

    $this->select->addOption(rex_i18n::msg($this->firstOptionMsg), 1);
    foreach($sql as $opt)
    {
      $this->select->addOption(
        rex_i18n::msg($this->optionMsg, $opt->getValue($this->labelField)),
        $opt->getValue($name)+1
      );
    }

    return parent::formatElement();
  }

  public function organizePriorities($params)
  {
    if($this->table->equals($params['form']))
    {
      $name = $this->getFieldName();

      rex_organize_priorities(
        $this->table->getTableName(),
        $name,
        $this->whereCondition,
        $name.', updatedate desc',
        $this->primaryKey
      );
    }
  }
}