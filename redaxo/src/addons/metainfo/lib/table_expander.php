<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\metainfo
 *
 * @internal
 */

class rex_metainfo_table_expander extends rex_form
{
    private $metaPrefix;
    private $tableManager;

    public function __construct($metaPrefix, $metaTable, $tableName, $whereCondition, $method = 'post', $debug = false)
    {
        $this->metaPrefix = $metaPrefix;
        $this->tableManager = new rex_metainfo_table_manager($metaTable);

        parent::__construct($tableName, rex_i18n::msg('minfo_field_fieldset'), $whereCondition, $method, $debug);
    }

    public function init()
    {
        // ----- EXTENSION POINT
        // IDs aller Feldtypen bei denen das Parameter-Feld eingeblendet werden soll
        $typeFields = rex_extension::registerPoint(new rex_extension_point('METAINFO_TYPE_FIELDS', [rex_metainfo_table_manager::FIELD_SELECT, rex_metainfo_table_manager::FIELD_RADIO, rex_metainfo_table_manager::FIELD_CHECKBOX, rex_metainfo_table_manager::FIELD_REX_MEDIA_WIDGET, rex_metainfo_table_manager::FIELD_REX_MEDIALIST_WIDGET, rex_metainfo_table_manager::FIELD_REX_LINK_WIDGET, rex_metainfo_table_manager::FIELD_REX_LINKLIST_WIDGET, rex_metainfo_table_manager::FIELD_DATE, rex_metainfo_table_manager::FIELD_DATETIME]));

        $field = $this->addReadOnlyField('prefix', $this->metaPrefix);
        $field->setLabel(rex_i18n::msg('minfo_field_label_prefix'));

        $field = $this->addTextField('name');
        $field->setLabel(rex_i18n::msg('minfo_field_label_name'));

        $field = $this->addSelectField('priority');
        $field->setLabel(rex_i18n::msg('minfo_field_label_priority'));
        $field->setAttribute('class', 'form-control selectpicker');
        $select = $field->getSelect();
        $select->setSize(1);
        $select->addOption(rex_i18n::msg('minfo_field_first_priority'), 1);
        // Im Edit Mode das Feld selbst nicht als Position einf�gen
        $qry = 'SELECT name,priority FROM ' . $this->tableName . ' WHERE `name` LIKE :name';
        $params = ['name' => $this->metaPrefix . '%'];
        if ($this->isEditMode()) {
            $qry .= ' AND id != :id';
            $params['id'] = $this->getParam('field_id');
        }
        $qry .= ' ORDER BY priority';
        $sql = rex_sql::factory();
        $sql->setQuery($qry, $params);
        $value = 1;
        for ($i = 0; $i < $sql->getRows(); ++$i) {
            $value = $sql->getValue('priority') + 1;
            $select->addOption(
                rex_i18n::rawMsg('minfo_field_after_priority', $sql->getValue('name')),
                $value
            );
            $sql->next();
        }
        if (!$this->isEditMode()) {
            $select->setSelected($value);
        }

        $field = $this->addTextField('title');
        $field->setLabel(rex_i18n::msg('minfo_field_label_title'));
        $field->setNotice(rex_i18n::msg('minfo_field_notice_title'));

        $gq = rex_sql::factory();
        $gq->setQuery('SELECT dbtype,id FROM ' . rex::getTablePrefix() . 'metainfo_type');
        $textFields = [];
        foreach ($gq->getArray() as $f) {
            if ('text' == $f['dbtype']) {
                $textFields[$f['id']] = $f['id'];
            }
        }

        $field = $this->addSelectField('type_id');
        $field->setLabel(rex_i18n::msg('minfo_field_label_type'));
        $field->setAttribute('class', 'form-control selectpicker');
        $field->setAttribute('onchange', 'meta_checkConditionalFields(this, new Array(' . implode(',', $typeFields) . '), new Array(' . implode(',', $textFields) . '));');
        $select = $field->getSelect();
        $select->setSize(1);

        $qry = 'SELECT label,id FROM ' . rex::getTablePrefix() . 'metainfo_type';
        $select->addSqlOptions($qry);

        $notices = '';
        for ($i = 1; $i < rex_metainfo_table_manager::FIELD_COUNT; ++$i) {
            if (rex_i18n::hasMsg('minfo_field_params_notice_' . $i)) {
                $notices .= '<span id="metainfo-field-params-notice-' . $i . '" style="display:none">' . rex_i18n::msg('minfo_field_params_notice_' . $i) . '</span>' . "\n";
            }
        }
        $notices .= '
        <script type="text/javascript">
            var needle = new getObj("' . $field->getAttribute('id') . '");
            meta_checkConditionalFields(needle.obj, new Array(' . implode(',', $typeFields) . '), new Array(' . implode(',', $textFields) . '));
        </script>';

        $field = $this->addTextAreaField('params');
        $field->setLabel(rex_i18n::msg('minfo_field_label_params'));
        $field->setNotice($notices);

        $field = $this->addTextAreaField('attributes');
        $field->setLabel(rex_i18n::msg('minfo_field_label_attributes'));
        $notice = rex_i18n::msg('minfo_field_attributes_notice') . "\n";
        $field->setNotice($notice);

        $field = $this->addTextAreaField('callback');
        $field->setLabel(rex_i18n::msg('minfo_field_label_callback'));
        $field->setAttribute('class', 'form-control rex-code rex-js-code');
        $notice = rex_i18n::msg('minfo_field_label_notice') . "\n";
        $field->setNotice($notice);

        $field = $this->addTextField('default');
        $field->setLabel(rex_i18n::msg('minfo_field_label_default'));

        if (rex_metainfo_clang_handler::PREFIX !== $this->metaPrefix) {
            $field = $this->addRestrictionsField('restrictions');
            $field->setLabel(rex_i18n::msg('minfo_field_label_restrictions'));
            $field->setAllCheckboxLabel(rex_i18n::msg('minfo_field_label_no_restrictions'));

            if (rex_metainfo_article_handler::PREFIX == $this->metaPrefix || rex_metainfo_category_handler::PREFIX == $this->metaPrefix) {
                $field->setSelect(new rex_category_select(false, false, true, false));
            } elseif (rex_metainfo_media_handler::PREFIX == $this->metaPrefix) {
                $field->setSelect(new rex_media_category_select());
            } else {
                throw new rex_exception('Unexpected TablePrefix "' . $this->metaPrefix . '".');
            }
        }

        if (rex_metainfo_article_handler::PREFIX === $this->metaPrefix && class_exists(rex_template_select::class)) {
            $field = $this->addRestrictionsField('templates');
            $field->setLabel(rex_i18n::msg('minfo_field_label_templates'));
            $field->setAllCheckboxLabel(rex_i18n::msg('minfo_field_label_all_templates'));
            $field->setSelect(new rex_template_select(null, rex_clang::getCurrentId()));
        }

        parent::init();
    }

    private function addRestrictionsField(string $name): rex_form_restrictons_element
    {
        /** @var rex_form_restrictons_element $field */
        $field = $this->addField('', $name, null, ['internal::fieldClass' => rex_form_restrictons_element::class]);
        $field->setAttribute('size', 10);
        $field->setAttribute('class', 'form-control');

        return $field;
    }

    protected function delete()
    {
        // Infos zuerst selektieren, da nach parent::delete() nicht mehr in der db
        $sql = rex_sql::factory();
        $sql->setDebug($this->debug);
        $sql->setTable($this->tableName);
        $sql->setWhere($this->whereCondition);
        $sql->select('name');
        $columnName = $sql->getValue('name');

        if (true === ($result = parent::delete())) {
            // Prios neu setzen, damit keine lücken entstehen
            $this->organizePriorities(1, 2);
            return $this->tableManager->deleteColumn($columnName);
        }

        return $result;
    }

    protected function preSave($fieldsetName, $fieldName, $fieldValue, rex_sql $saveSql)
    {
        if ($fieldsetName == $this->getFieldsetName() && 'name' == $fieldName) {
            // Den Namen mit Prefix speichern
            return $this->addPrefix($fieldValue);
        }

        return parent::preSave($fieldsetName, $fieldName, $fieldValue, $saveSql);
    }

    protected function preView($fieldsetName, $fieldName, $fieldValue)
    {
        if ($fieldsetName == $this->getFieldsetName() && 'name' == $fieldName) {
            // Den Namen ohne Prefix anzeigen
            return $this->stripPrefix($fieldValue);
        }
        return parent::preView($fieldsetName, $fieldName, $fieldValue);
    }

    public function addPrefix($string)
    {
        $lowerString = strtolower($string);
        if (!str_starts_with($lowerString, $this->metaPrefix)) {
            return $this->metaPrefix . $string;
        }
        return $string;
    }

    public function stripPrefix($string)
    {
        $lowerString = strtolower($string);
        if (str_starts_with($lowerString, $this->metaPrefix)) {
            return substr($string, strlen($this->metaPrefix));
        }
        return $string;
    }

    /**
     * @return bool|string
     */
    protected function validate()
    {
        $fieldName = $this->elementPostValue($this->getFieldsetName(), 'name');
        if ('' == $fieldName) {
            return rex_i18n::msg('minfo_field_error_name');
        }

        if (preg_match('/[^a-zA-Z0-9\_]/', $fieldName)) {
            return rex_i18n::msg('minfo_field_error_chars_name');
        }

        // Pruefen ob schon eine Spalte mit dem Namen existiert (nur beim add noetig)
        if (!$this->isEditMode()) {
            // die tabelle selbst checken
            if ($this->tableManager->hasColumn($this->addPrefix($fieldName))) {
                return rex_i18n::msg('minfo_field_error_unique_name');
            }

            // das meta-schema checken
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM ' . $this->tableName . ' WHERE name = ? LIMIT 1', [$this->addPrefix($fieldName)]);
            if (1 == $sql->getRows()) {
                return rex_i18n::msg('minfo_field_error_unique_name');
            }
        }

        return parent::validate();
    }

    protected function save()
    {
        $fieldName = $this->elementPostValue($this->getFieldsetName(), 'name');

        // Den alten Wert aus der DB holen
        // Dies muss hier geschehen, da in parent::save() die Werte fuer die DB mit den
        // POST werten ueberschrieben werden!
        $fieldOldName = '';
        $fieldOldPriority = 9999999999999; // dirty, damit die prio richtig l�uft...
        if (1 == $this->sql->getRows()) {
            $fieldOldName = $this->sql->getValue('name');
            $fieldOldPriority = $this->sql->getValue('priority');
        }

        if (parent::save()) {
            $this->organizePriorities($this->elementPostValue($this->getFieldsetName(), 'priority'), $fieldOldPriority);

            $fieldName = $this->addPrefix($fieldName);
            $fieldType = (int) $this->elementPostValue($this->getFieldsetName(), 'type_id');
            $fieldDefault = $this->elementPostValue($this->getFieldsetName(), 'default');
            $fieldAttributes = $this->elementPostValue($this->getFieldsetName(), 'attributes');

            $sql = rex_sql::factory();
            $sql->setDebug($this->debug);
            $result = $sql->getArray('SELECT `dbtype`, `dblength` FROM `' . rex::getTablePrefix() . 'metainfo_type` WHERE id=' . $fieldType);
            $fieldDbType = $result[0]['dbtype'];
            $fieldDbLength = $result[0]['dblength'];

            if (
                strlen($fieldDefault) &&
                (rex_metainfo_table_manager::FIELD_CHECKBOX === $fieldType || rex_metainfo_table_manager::FIELD_SELECT === $fieldType && isset(rex_string::split($fieldAttributes)['multiple']))
            ) {
                $fieldDefault = '|'.trim($fieldDefault, '|').'|';
            }

            if ($this->isEditMode()) {
                // Spalte in der Tabelle ver�ndern
                $tmRes = $this->tableManager->editColumn($fieldOldName, $fieldName, $fieldDbType, $fieldDbLength, $fieldDefault);
            } else {
                // Spalte in der Tabelle anlegen
                $tmRes = $this->tableManager->addColumn($fieldName, $fieldDbType, $fieldDbLength, $fieldDefault);
            }
            rex_delete_cache();

            return $tmRes;
        }

        return false;
    }

    public function getPrefix()
    {
        return $this->metaPrefix;
    }

    protected function organizePriorities($newPrio, $oldPrio)
    {
        if ($newPrio == $oldPrio) {
            return;
        }

        // replace LIKE wildcards
        $metaPrefix = str_replace(['_', '%'], ['\_', '\%'], $this->metaPrefix);

        rex_sql_util::organizePriorities(
            $this->tableName,
            'priority',
            'name LIKE "' . $metaPrefix . '%"',
            'priority, updatedate desc'
        );
    }
}
