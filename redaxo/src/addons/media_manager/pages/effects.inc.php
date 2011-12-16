<?php

$Basedir = dirname(__FILE__);

$effect_id = rex_request('effect_id','int');
$type_id = rex_request('type_id','int');
$func = rex_request('func','string');

// ---- validate type_id
$sql = rex_sql::factory();
$sql->setQuery('SELECT * FROM '. rex::getTablePrefix().'media_manager_types WHERE id='. $type_id);
if($sql->getRows() != 1)
{
  unset($type_id);
}
$typeName = $sql->getValue('name');


$info = '';
$warning = '';

//-------------- delete cache on effect changes or deletion
if((rex_post('func') != '' || $func == 'delete')
   && $type_id > 0)
{
  $counter = rex_media_manager::deleteCacheByType($type_id);
//  $info = rex_i18n::msg('media_manager_cache_files_removed', $counter);
}

//-------------- delete effect
if($func == 'delete' && $effect_id > 0)
{
  $sql = rex_sql::factory();
//  $sql->debugsql = true;
  $sql->setTable(rex::getTablePrefix().'media_manager_type_effects');
  $sql->setWhere(array('id' => $effect_id));

  if($sql->delete())
  {
     $info = rex_i18n::msg('media_manager_effect_deleted') ;
  }
  else
  {
    $warning = $sql->getErrro();
  }
  $func = '';
}

if ($info != '')
  echo rex_view::info($info);

if ($warning != '')
  echo rex_view::warning($warning);


echo '<div class="rex-addon-output-v2">';
if ($func == '' && $type_id > 0)
{
  echo rex_view::contentBlock(rex_i18n::msg('media_manager_effect_list_header', htmlspecialchars($typeName)));

  $query = 'SELECT * FROM '.rex::getTablePrefix().'media_manager_type_effects WHERE type_id='.$type_id .' ORDER BY prior';

	$list = rex_list::factory($query);
  $list->setNoRowsMessage(htmlspecialchars(rex_i18n::msg('media_manager_effect_no_effects')));
  $list->setCaption(htmlspecialchars(rex_i18n::msg('media_manager_effect_caption', $typeName)));
  $list->addTableAttribute('summary', htmlspecialchars(rex_i18n::msg('media_manager_effect_summary', $typeName)));
  $list->addTableColumnGroup(array(40, '*', 40, 130, 130));

	$list->removeColumn('id');
	$list->removeColumn('type_id');
	$list->removeColumn('parameters');
	$list->removeColumn('updatedate');
	$list->removeColumn('updateuser');
	$list->removeColumn('createdate');
	$list->removeColumn('createuser');
	$list->setColumnLabel('effect',htmlspecialchars(rex_i18n::msg('media_manager_type_name')));
	$list->setColumnLabel('prior',htmlspecialchars(rex_i18n::msg('media_manager_type_prior')));

	// icon column
  $thIcon = '<a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('type_id' => $type_id, 'func' => 'add')) .'"><span class="rex-i-element-text">'. htmlspecialchars(rex_i18n::msg('media_manager_effect_create')) .'</span></a>';
  $tdIcon = '<span class="rex-i-element rex-i-generic"><span class="rex-i-element-text">###name###</span></span>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('func' => 'edit', 'type_id' => $type_id, 'effect_id' => '###id###'));

  // functions column spans 2 data-columns
  $funcs = rex_i18n::msg('media_manager_effect_functions');
  $list->addColumn($funcs, rex_i18n::msg('media_manager_effect_edit'), -1, array('<th colspan="2">###VALUE###</th>','<td>###VALUE###</td>'));
  $list->setColumnParams($funcs, array('func' => 'edit', 'type_id' => $type_id, 'effect_id' => '###id###'));

  $delete = 'deleteCol';
  $list->addColumn($delete, rex_i18n::msg('media_manager_effect_delete'), -1, array('','<td>###VALUE###</td>'));
  $list->setColumnParams($delete, array('type_id' => $type_id, 'effect_id' => '###id###', 'func' => 'delete'));
  $list->addLinkAttribute($delete, 'onclick', 'return confirm(\''.rex_i18n::msg('delete').' ?\')');

	$list->show();
}
elseif ($func == 'add' && $type_id > 0 ||
        $func == 'edit' && $effect_id > 0 && $type_id > 0)
{
  $effectNames = rex_media_manager::getSupportedEffectNames();

  if($func == 'edit')
  {
    $formLabel = rex_i18n::msg('media_manager_effect_edit_header', htmlspecialchars($typeName));
  }
  else if ($func == 'add')
  {
    $formLabel = rex_i18n::msg('media_manager_effect_create_header', htmlspecialchars($typeName));
  }

	$form = rex_form::factory(rex::getTablePrefix().'media_manager_type_effects',$formLabel,'id='.$effect_id);

	// image_type_id for reference to save into the db
  $form->addHiddenField('type_id', $type_id);

	// effect name als SELECT
	$field = $form->addSelectField('effect');
	$field->setLabel(rex_i18n::msg('media_manager_effect_name'));
	$select = $field->getSelect();
	$select->addOptions($effectNames, true);
	$select->setSize(1);

	$script = '
  <script type="text/javascript">
  <!--

  (function($) {
    var currentShown = null;
    $("#'. $field->getAttribute('id') .'").change(function(){
      if(currentShown) currentShown.hide();

      var effectParamsId = "#rex-rex_effect_"+ jQuery(this).val();
      currentShown = $(effectParamsId);
      currentShown.show();
    }).change();
  })(jQuery);

  //--></script>';

  // effect prio
  $field = $form->addPrioField('prior');
  $field->setLabel(rex_i18n::msg('media_manager_effect_prior'));
  $field->setLabelField('effect');
  $field->setWhereCondition('type_id = '. $type_id);

  // effect parameters
	$fieldContainer = $form->addContainerField('parameters');
  $fieldContainer->setAttribute('style', 'display: none');
	$fieldContainer->setSuffix($script);

  $effects = rex_media_manager::getSupportedEffects();

  foreach($effects as $effectClass => $effectFile)
  {
    require_once($effectFile);
    $effectObj = new $effectClass();
    $effectParams = $effectObj->getParams();
    $group = $effectClass;

    if(empty($effectParams)) continue;

    foreach($effectParams as $param)
    {
      $name = $effectClass.'_'.$param['name'];
      $value = isset($param['default']) ? $param['default'] : null;
      $attributes = array();
      if (isset($param['attributes']))
        $attributes = $param['attributes'];

      switch($param['type'])
      {
        case 'int' :
        case 'float' :
        case 'string' :
          {
            $type = 'text';
            $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
            $field->setLabel($param['label']);
            $field->setAttribute('id',"image_manager $name $type");
            if(!empty($param['notice'])) $field->setNotice($param['notice']);
            if(!empty($param['prefix'])) $field->setPrefix($param['prefix']);
            if(!empty($param['suffix'])) $field->setSuffix($param['suffix']);
            break;
          }
        case 'select' :
          {
            $type = $param['type'];
            $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
            $field->setLabel($param['label']);
            $field->setAttribute('id',"image_manager $name $type");
            if(!empty($param['notice'])) $field->setNotice($param['notice']);
            if(!empty($param['prefix'])) $field->setPrefix($param['prefix']);
            if(!empty($param['suffix'])) $field->setSuffix($param['suffix']);

            $select = $field->getSelect();
            if (!isset($attributes['multiple']))
              $select->setSize(1);
            $select->addOptions($param['options'], true);
            break;
          }
        case 'media' :
          {
            $type = $param['type'];
            $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
            $field->setLabel($param['label']);
            $field->setAttribute('id',"image_manager $name $type");
            if(!empty($param['notice'])) $field->setNotice($param['notice']);
            if(!empty($param['prefix'])) $field->setPrefix($param['prefix']);
            if(!empty($param['suffix'])) $field->setSuffix($param['suffix']);
            break;
          }
        default:
          {
            trigger_error('Unexpected param type "'. $param['type'] .'"', E_USER_ERROR);
          }
      }
    }
  }

  // parameters for url redirects
	$form->addParam('type_id', $type_id);
	if($func == 'edit')
	{
		$form->addParam('effect_id', $effect_id);
	}
	$form->show();
}

echo '</div>';
?>