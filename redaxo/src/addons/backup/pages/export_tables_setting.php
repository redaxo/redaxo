<?php
// Save Export-Table-Settings
if (rex_post('formsubmit', 'int') === 1) {
	$this->setConfig('export_tables_selected', rex_post('EXPTABLES', 'array') );
}


$content = '';
$buttons = '';

$content .= '<fieldset><legend>'.rex_i18n::msg('backup_export_select_tables_desc').'</legend>';

$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mform-config-template">'.rex_i18n::msg('backup_export_select_tables').'</label>
';

// create select
$select = new rex_select;
$select->setMultiple();
$select->setId('rex-form-exporttables');
$select->setSize(20);
$select->setAttribute('class', 'form-control');
$select->setName('EXPTABLES[]');
// add options
$sql_tables = rex_sql::factory();
$tables = $sql_tables->getTablesAndViews();
foreach ($tables as $table) {
    $select->addOption($table,$table);
        foreach ($this->getConfig('export_tables_selected') as $table_selected) {
            if($table == $table_selected) {
                $select->setSelected($table);
            } 
        }
}
//$select->setSelected($this->getConfig('editor'));
$elements['field'] = $select->get();
$formElements[] = $elements;
// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';



// Save-Button
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="Speichern">'.rex_i18n::msg('backup_export_select_tables_save').'</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');
$buttons = '
<fieldset class="rex-form-action">
    ' . $buttons . '
</fieldset>
';

// Ausgabe Formular
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', rex_i18n::msg('backup_export_select_tables_heading'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$output = $fragment->parse('core/page/section.php');

$output = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="formsubmit" value="1" />
    ' . $output . '
</form>
';

echo $output;	
?>

