<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

if ($func == 'setstatus')
{
  $manager = rex_cronjob_manager_sql::factory();
  $name = $manager->getName($oid);
  $status = (rex_request('oldstatus', 'int') +1) % 2;
  $msg = $status == 1 ? 'cronjob_status_activate' : 'cronjob_status_deactivate';
  if ($manager->setStatus($oid, $status))
    echo rex_info($I18N->msg($msg .'_success', $name));
  else
    echo rex_warning($I18N->msg($msg .'_error', $name));
  $func = '';
}

if ($func == 'delete')
{
  $manager = rex_cronjob_manager_sql::factory();
  $name = $manager->getName($oid);
  if ($manager->delete($oid))
    echo rex_info($I18N->msg('cronjob_delete_success', $name));
  else
    echo rex_warning($I18N->msg('cronjob_delete_error', $name));
  $func = '';
}

if ($func == 'execute')
{
  $manager = rex_cronjob_manager_sql::factory();
  $name = $manager->getName($oid);
  $success = $manager->tryExecute($oid);
  $msg = '';
  if ($manager->hasMessage())
    $msg = '<br /><br />'. $I18N->msg('cronjob_log_message') .': <br />'. nl2br($manager->getMessage());
  if ($success)
    echo rex_info($I18N->msg('cronjob_execute_success', $name) . $msg);
  else
    echo rex_warning($I18N->msg('cronjob_execute_error', $name) . $msg);
  $func = '';
}

if ($func == '') 
{

  $query = 'SELECT id, name, type, `interval`, environment, status FROM '. REX_CRONJOB_TABLE .' ORDER BY name';
  
  $list = rex_list::factory($query, 30, 'cronjobs');
  
  $list->setNoRowsMessage($I18N->msg('cronjob_no_cronjobs'));
  $list->setCaption($I18N->msg('cronjob_caption'));
  $list->addTableAttribute('summary', $I18N->msg('cronjob_summary'));
  
  $list->addTableColumnGroup(array(40,'*',90,130,60,60,60));
  
  $imgHeader = '<a class="rex-i-element rex-i-cronjob-add" href="'. $list->getUrl(array('func' => 'add')) .'"><span class="rex-i-element-text">'. $I18N->msg('cronjob_add') .'</span></a>';
  $list->addColumn($imgHeader, '<span class="rex-i-element rex-i-cronjob"><span class="rex-i-element-text">'. $I18N->msg('cronjob_edit') .'</span></span>', 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($imgHeader, array('func' => 'edit', 'oid' => '###id###'));
  
  $list->removeColumn('id');
  $list->removeColumn('type');
  
  $list->setColumnLabel('name', $I18N->msg('cronjob_name'));
  $list->setColumnParams('name', array('func'=>'edit', 'oid'=>'###id###'));
  
  $list->setColumnLabel('interval', $I18N->msg('cronjob_interval'));
  $list->setColumnFormat('interval', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       $value = explode("|",$list->getValue("interval"));
       $str = $value[1]." ";
       $array = array("h"=>"hour", "d"=>"day", "w"=>"week", "m"=>"month", "y"=>"year");
       $str .= $I18N->msg("cronjob_interval_".$array[$value[2]]);
       return $str;' 
    ) 
  );
  
  $list->setColumnLabel('environment', $I18N->msg('cronjob_environment'));
  $list->setColumnFormat('environment', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"];
       $value = $list->getValue("environment");
       $env = array();
       if (strpos($value, "|0|") !== false) 
         $env[] = $I18N->msg("cronjob_environment_frontend");
       if (strpos($value, "|1|") !== false) 
         $env[] = $I18N->msg("cronjob_environment_backend");
       return implode(", ", $env);' 
    ) 
  );
  
  $list->setColumnLabel('status', $I18N->msg('cronjob_status_function'));
  $list->setColumnParams('status', array('func'=>'setstatus', 'oldstatus'=>'###status###', 'oid'=>'###id###'));
  $list->setColumnLayout('status', array('<th colspan="3">###VALUE###</th>','<td style="text-align:center;">###VALUE###</td>'));
  $list->setColumnFormat('status', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       if (!class_exists($list->getValue("type")))
         $str = $I18N->msg("cronjob_status_invalid");
       elseif ($list->getValue("status") == 1) 
         $str = $list->getColumnLink("status","<span class=\"rex-online\">".$I18N->msg("cronjob_status_activated")."</span>"); 
       else 
         $str = $list->getColumnLink("status","<span class=\"rex-offline\">".$I18N->msg("cronjob_status_deactivated")."</span>"); 
       return $str;' 
    ) 
  );
  
  $list->addColumn('delete', $I18N->msg('cronjob_delete'), -1, array("",'<td style="text-align:center;">###VALUE###</td>'));
  $list->setColumnParams('delete', array('func' => 'delete', 'oid' => '###id###'));
  $list->addLinkAttribute('delete', 'onclick', "return confirm('". $I18N->msg('cronjob_really_delete') ."');");
  
  $list->addColumn('execute', $I18N->msg('cronjob_execute'), -1, array("",'<td style="text-align:center;">###VALUE###</td>'));
  $list->setColumnParams('execute', array('func' => 'execute', 'oid' => '###id###'));
  $list->setColumnFormat('execute', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       if (strpos($list->getValue("environment"),"|1|") !== false && class_exists($list->getValue("type")))
         return $list->getColumnLink("execute",$I18N->msg("cronjob_execute"));
       return "<span class=\"rex-strike\">".$I18N->msg("cronjob_execute")."</span>";' 
    ) 
  );
  
  $list->show();
  
} elseif ($func == 'edit' || $func == 'add') 
{
  require_once $REX['INCLUDE_PATH'].'/addons/cronjob/classes/class.form.inc.php';
  
  $fieldset = $func == 'edit' ? $I18N->msg('cronjob_edit') : $I18N->msg('cronjob_add');
  
  $form = rex_form::factory(REX_CRONJOB_TABLE, $fieldset, 'id = '. $oid, 'post', false, 'rex_cronjob_form');
  $form->addParam('oid', $oid);
  $form->setApplyUrl('index.php?page=cronjob');
  $form->setEditMode($func == 'edit');
  
  $field =& $form->addSelectField('type');
  $field->setLabel($I18N->msg('cronjob_type'));
  $select =& $field->getSelect();
  $select->setSize(1);
  $typeFieldId = $field->getAttribute('id');
  $types = rex_cronjob_manager::getTypes();
  $cronjobs = array();
  foreach($types as $class)
  {
    $cronjob = rex_cronjob::factory($class);
    if (rex_cronjob::isValid($cronjob))
    {
      $cronjobs[$class] = $cronjob;
      $select->addOption($cronjob->getTypeName(), $class);
    }
  }
  if ($func == 'add')
    $select->setSelected('rex_cronjob_phpcode');
  $activeType = $field->getValue();
  
  $field =& $form->addTextField('name');
  $field->setLabel($I18N->msg('cronjob_name'));
  $nameFieldId = $field->getAttribute('id');
  
  if ($func != 'add' && !in_array($activeType, $types)) 
  {
    if (!$activeType && !$field->getValue())
      $warning = $I18N->msg('cronjob_not_found');
    else
      $warning = $I18N->msg('cronjob_type_not_found', $field->getValue(), $activeType);
    header('Location: index.php?page=cronjob&'. rex_request('list', 'string') .'_warning='. $warning);
    exit;
  }
  
  $field =& $form->addIntervalField('interval');
  $field->setLabel($I18N->msg('cronjob_interval'));
  
  $field =& $form->addSelectField('environment');
  $field->setLabel($I18N->msg('cronjob_environment'));
  $field->setAttribute('multiple', 'multiple');
  $envFieldId = $field->getAttribute('id');
  $select =& $field->getSelect();
  $select->setSize(2);
  $select->addOption($I18N->msg('cronjob_environment_frontend'),0);
  $select->addOption($I18N->msg('cronjob_environment_backend'),1);
  if ($func == 'add')
    $select->setSelected(array(0,1));
   
  $field =& $form->addSelectField('status');
  $field->setLabel($I18N->msg('cronjob_status'));
  $select =& $field->getSelect();
  $select->setSize(1);
  $select->addOption($I18N->msg('cronjob_status_activated'),1);
  $select->addOption($I18N->msg('cronjob_status_deactivated'),0);
  if ($func == 'add')
    $select->setSelected(1);
  
  $form->addFieldset($I18N->msg('cronjob_type_parameters')); 
  
  $fieldContainer =& $form->addContainerField('parameters');
  $fieldContainer->setAttribute('style', 'display: none');
  $fieldContainer->setMultiple(false);
  $fieldContainer->setActive($activeType);
  
  $env_js = '';
  $visible = array();
  foreach ($cronjobs as $group => $cronjob)
  {
    $disabled = array();
    $envs = (array) $cronjob->getEnvironments();
    if (!in_array('frontend', $envs))
      $disabled[] = 0;
    if (!in_array('backend', $envs))
      $disabled[] = 1;
    if (count($disabled) > 0)
      $env_js .= '
        if ($("#'. $typeFieldId .' option:selected").val() == "'. $group .'")
          $("#'. $envFieldId .' option[value=\''. implode('\'], #'. $envFieldId .' option[value=\'', $disabled) .'\']").attr("disabled","disabled").attr("selected","");
';
  
    $params = $cronjob->getParamFields();
    
    if (!is_array($params) || empty($params)) {
      $field =& $fieldContainer->addGroupedField($group, 'readonly', 'noparams', $I18N->msg('cronjob_type_no_parameters'));
      $field->setLabel('&nbsp;');
    } else {
      foreach($params as $param)
      {
        $type = $param['type'];
        $name = $group .'_'. $param['name'];
        $value = isset($param['default']) ? $param['default'] : null;
        $attributes = isset($param['attributes']) ? $param['attributes'] : array();
        switch($param['type'])
        {
          case 'text' :
          case 'textarea' :
          case 'media' :
          case 'medialist' :
          case 'link' :
          case 'linklist' :
            {
              $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
              $field->setLabel($param['label']);
              if (isset($param['notice']))
                $field->setNotice($param['notice']);
              break;
            }
          case 'select' :
            {
              $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
              $field->setLabel($param['label']);
              $select =& $field->getSelect();
              $select->addArrayOptions($param['options']);
              if (isset($param['notice']))
                $field->setNotice($param['notice']);
              break;
            }
          case 'checkbox' :
          case 'radio' :
            {
              $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
              $field->addArrayOptions($param['options']);
              if (isset($param['notice']))
                $field->setNotice($param['notice']);
              break;
            }
          default:var_dump($param);
        }
        if (isset($param['visible_if']) && is_array($param['visible_if']))
        {
          foreach($param['visible_if'] as $key => $value)
          {
            $key = $group .'_'. $key;
            if (!isset($visible[$key]))
              $visible[$key] = array();
            if (!isset($visible[$key][$value]))
              $visible[$key][$value] = array();
            $visible[$key][$value][] = $field->getAttribute('id');
          }
        }
      }
    }
  }
  $visible_js = '';
  if(!empty($visible))
  {
    foreach($fieldContainer->getFields() as $group => $fieldElements)
    {
      foreach($fieldElements as $field)
      {
        $name = $field->getFieldName();
        if(isset($visible[$name]))
        {
          foreach($visible[$name] as $value => $fieldIds)
          {
            $visible_js .= '
            var first = 1;
            $("#'.$field->getAttribute('id').'_'.$value.'").change(function(){
              var checkbox = $(this);
              $("#'.implode(',#',$fieldIds).'").each(function(){
                if ($(checkbox).is(":checked"))
                  $(this).parent().parent().slideDown();
                else if(first == 1)
                  $(this).parent().parent().hide();
                else
                  $(this).parent().parent().slideUp();
              });
              first = 0;
            }).change();';
          }
        }
      }
    }
  }
  
  $form->addHiddenField('nexttime', 0);
  
  $form->show();

?>
  
  <script type="text/javascript">
  // <![CDATA[
    jQuery(function($){
      var currentShown = null;
      $("#<?php echo $typeFieldId ?>").change(function(){
        if(currentShown) currentShown.hide();
        var typeId = "#rex-"+ $(this).val();
        currentShown = $(typeId);
        currentShown.show();
      }).change();
      $('#<?php echo $typeFieldId ?>').change(function(){
        $('#<?php echo $envFieldId ?> option').attr('disabled','');<?php echo $env_js; ?>
      }).change();<?php echo $visible_js."\n"; ?>
    });
  // ]]>
  </script>
  
<?php 

}