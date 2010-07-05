<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_a62_tableExpander extends rex_form
{
  var $metaPrefix;
  var $tableManager;

  /*public*/ function rex_a62_tableExpander($metaPrefix, $metaTable, $tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
  {
    $this->metaPrefix = $metaPrefix;
    $this->tableManager = new rex_a62_tableManager($metaTable);

    parent::rex_form($tableName, $fieldset, $whereCondition, $method, $debug);
  }

  /*public*/ function init()
  {
    global $REX, $I18N;

    // ----- EXTENSION POINT
    // IDs aller Feldtypen bei denen das Parameter-Feld eingeblendet werden soll
    $typeFields = rex_register_extension_point( 'A62_TYPE_FIELDS', array(REX_A62_FIELD_SELECT, REX_A62_FIELD_RADIO, REX_A62_FIELD_CHECKBOX, REX_A62_FIELD_REX_MEDIA_BUTTON, REX_A62_FIELD_REX_MEDIALIST_BUTTON, REX_A62_FIELD_REX_LINK_BUTTON, REX_A62_FIELD_REX_LINKLIST_BUTTON));

    $field =& $this->addReadOnlyField('prefix', $this->metaPrefix);
    $field->setLabel($I18N->msg('minfo_field_label_prefix'));

    $field =& $this->addTextField('name');
    $field->setLabel($I18N->msg('minfo_field_label_name'));

    $field =& $this->addSelectField('prior');
    $field->setLabel($I18N->msg('minfo_field_label_prior'));
    $select =& $field->getSelect();
    $select->setSize(1);
    $select->addOption($I18N->msg('minfo_field_first_prior'), 1);
    // Im Edit Mode das Feld selbst nicht als Position einfügen
    $qry = 'SELECT name,prior FROM '. $this->tableName .' WHERE `name` LIKE "'. $this->metaPrefix .'%"';
    if($this->isEditMode())
    {
      $qry .= ' AND field_id != '. $this->getParam('field_id');
    }
    $qry .=' ORDER BY prior';
    $sql = rex_sql::factory();
    $sql->setQuery($qry);
    for($i = 0; $i < $sql->getRows(); $i++)
    {
      $select->addOption(
        $I18N->msg('minfo_field_after_prior', $sql->getValue('name')),
        $sql->getValue('prior')+1
      );
      $sql->next();
    }

    $field =& $this->addTextField('title');
    $field->setLabel($I18N->msg('minfo_field_label_title'));
    $field->setNotice($I18N->msg('minfo_field_notice_title'));

	  $gq = new rex_sql;
		$gq->setQuery('SELECT dbtype,id FROM '. $REX['TABLE_PREFIX'] .'62_type');
		$textFields = array();
		foreach($gq->getArray() as $f) 
		{
		  if($f["dbtype"] == "text")
		  {
		  $textFields[$f['id']] = $f['id'];
		  }
		}
    
    $field =& $this->addSelectField('type');
    $field->setLabel($I18N->msg('minfo_field_label_type'));
    $field->setAttribute('onchange', 'meta_checkConditionalFields(this, new Array('. implode(',', $typeFields) .'), new Array('. implode(',', $textFields) .'));');
    $select =& $field->getSelect();
    $select->setSize(1);

    $changeTypeFieldId = $field->getAttribute('id');
    
    $qry = 'SELECT label,id FROM '. $REX['TABLE_PREFIX'] .'62_type';
    $select->addSqlOptions($qry);

    $notices = '';
    for($i = 1; $i < REX_A62_FIELD_COUNT; $i++)
    {
      if($I18N->hasMsg('minfo_field_params_notice_'. $i))
      {
        $notices .= '<span class="rex-form-notice" id="a62_field_params_notice_'. $i .'" style="display:none">'. $I18N->msg('minfo_field_params_notice_'. $i) .'</span>'. "\n";
      }
    }
    $notices .= '
    <script type="text/javascript">
      var needle = new getObj("'. $field->getAttribute('id') .'");
      meta_checkConditionalFields(needle.obj, new Array('. implode(',', $typeFields) .'), new Array('. implode(',', $textFields) .'));
    </script>';

    $field =& $this->addTextAreaField('params');
    $field->setLabel($I18N->msg('minfo_field_label_params'));
    $field->setSuffix($notices);

    $field =& $this->addTextAreaField('attributes');
    $field->setLabel($I18N->msg('minfo_field_label_attributes'));
    $notice = '<span class="rex-form-notice" id="a62_field_attributes_notice">'. $I18N->msg('minfo_field_attributes_notice') .'</span>'. "\n";
    $field->setSuffix($notice);

    $field =& $this->addTextField('default');
    $field->setLabel($I18N->msg('minfo_field_label_default'));

    $attributes = array();
    $attributes['internal::fieldClass'] = 'rex_form_restrictons_element';
    $field =& $this->addField('', 'restrictions', $value = null, $attributes);
    $field->setLabel($I18N->msg('minfo_field_label_restrictions'));
    $field->setAttribute('size', 10);
    
    parent::init();
  }

  /*public*/ function getFieldsetName()
  {
    global $I18N;
    return $I18N->msg('minfo_field_fieldset');
  }

  /*protected*/ function delete()
  {
  	// Infos zuerst selektieren, da nach parent::delete() nicht mehr in der db
    $sql = rex_sql::factory();
    $sql->debugsql =& $this->debug;
    $sql->setTable($this->tableName);
    $sql->setWhere($this->whereCondition);
    $sql->select('name');
    $columnName = $sql->getValue('name');
    
    if(($result = parent::delete()) === true)
    {
      // Prios neu setzen, damit keine lücken entstehen
      $this->organizePriorities(1,2);
      return $this->tableManager->deleteColumn($columnName);
    }
    
    return $result;
  }

  /*protected*/ function preSave($fieldsetName, $fieldName, $fieldValue, &$saveSql)
  {
    global $REX;

    if($fieldsetName == $this->getFieldsetName() && $fieldName == 'name')
    {
      // Den Namen mit Prefix speichern
      return $this->addPrefix($fieldValue);
    }
    
    return parent::preSave($fieldsetName, $fieldName, $fieldValue, $saveSql);
  }

  /*protected*/ function preView($fieldsetName, $fieldName, $fieldValue)
  {
    if($fieldsetName == $this->getFieldsetName() && $fieldName == 'name')
    {
      // Den Namen ohne Prefix anzeigen
      return $this->stripPrefix($fieldValue);
    }
    return parent::preView($fieldsetName, $fieldName, $fieldValue);
  }

  /*public*/ function addPrefix($string)
  {
    $lowerString = strtolower($string);
    if(substr($lowerString, 0, strlen($this->metaPrefix)) !== $this->metaPrefix)
    {
      return $this->metaPrefix . $string;
    }
    return $string;
  }

  /*public*/ function stripPrefix($string)
  {
    $lowerString = strtolower($string);
    if(substr($lowerString, 0, strlen($this->metaPrefix)) === $this->metaPrefix)
    {
      return substr($string, strlen($this->metaPrefix));
    }
    return $string;
  }

  /*protected*/ function validate()
  {
    global $I18N;

    $fieldName = $this->elementPostValue($this->getFieldsetName(), 'name');
    if($fieldName == '')
      return $I18N->msg('minfo_field_error_name');

    if(preg_match('/[^a-zA-Z0-9\_]/', $fieldName))
      return $I18N->msg('minfo_field_error_chars_name');

    // Prüfen ob schon eine Spalte mit dem Namen existiert (nur beim add nötig)
    if(!$this->isEditMode())
    {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT * FROM '. $this->tableName .' WHERE name="'. $this->addPrefix($fieldName) .'" LIMIT 1');
      if($sql->getRows() == 1)
      {
        return $I18N->msg('minfo_field_error_unique_name');
      }
    }

    return parent::validate();
  }

  /*protected*/ function save()
  {
    $fieldName = $this->elementPostValue($this->getFieldsetName(), 'name');

    // Den alten Wert aus der DB holen
    // Dies muss hier geschehen, da in parent::save() die Werte für die DB mit den
    // POST werten überschrieben werden!
    $fieldOldName = '';
    $fieldOldPrior = 9999999999999; // dirty, damit die prio richtig läuft...
    $fieldOldDefault = '';
    if($this->sql->getRows() == 1)
    {
      $fieldOldName = $this->sql->getValue('name');
      $fieldOldPrior = $this->sql->getValue('prior');
      $fieldOldDefault = $this->sql->getValue('default');
    }

    if(parent::save())
    {
      global $REX, $I18N;

      $this->organizePriorities($this->elementPostValue($this->getFieldsetName(), 'prior'), $fieldOldPrior);
      rex_generateAll();

      $fieldName = $this->addPrefix($fieldName);
      $fieldType = $this->elementPostValue($this->getFieldsetName(), 'type');
      $fieldDefault = $this->elementPostValue($this->getFieldsetName(), 'default');

      $sql = rex_sql::factory();
      $sql->debugsql =& $this->debug;
      $result = $sql->getArray('SELECT `dbtype`, `dblength` FROM `'. $REX['TABLE_PREFIX'] .'62_type` WHERE id='. $fieldType);
      $fieldDbType = $result[0]['dbtype'];
      $fieldDbLength = $result[0]['dblength'];
      
      // TEXT Spalten dürfen in MySQL keine Defaultwerte haben
      if($fieldDbType == 'text')
        $fieldDefault = null;

      if($this->isEditMode())
      {
        // Spalte in der Tabelle verändern
        $tmRes = $this->tableManager->editColumn($fieldOldName, $fieldName, $fieldDbType, $fieldDbLength, $fieldDefault);
      }
      else
      {
        // Spalte in der Tabelle anlegen
        $tmRes = $this->tableManager->addColumn($fieldName, $fieldDbType, $fieldDbLength, $fieldDefault);
      }
      
      if($tmRes)
      {
        // DefaultWerte setzen
        if($fieldDefault != $fieldOldDefault)
        {
          $upd = rex_sql::factory();
          $upd->debugsql =& $this->debug;
          $upd->setTable($this->tableManager->getTableName());
          $upd->setWhere('`'. $fieldName .'`="'. addSlashes($fieldOldDefault) .'"');
          $upd->setValue($fieldName, addSlashes($fieldDefault));
          return $upd->update();
        }
        
        // Default werte haben schon zuvor gepasst, daher true zurückgeben
        return true;
      }
    }

    return false;
  }
  
  /*public*/ function getPrefix()
  {
    return $this->metaPrefix;
  }

  /*protected*/ function organizePriorities($newPrio, $oldPrio)
  {
    if($newPrio == $oldPrio)
      return;

    rex_organize_priorities(
      $this->tableName,
      'prior',
      'name LIKE "'. $this->metaPrefix .'%"',
      'prior, updatedate desc', 
      'field_id'
    );
  }
}