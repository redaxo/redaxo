<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

rex_register_extension('CAT_FORM_ADD', 'rex_a62_metainfo_form');
rex_register_extension('CAT_FORM_EDIT', 'rex_a62_metainfo_form');

rex_register_extension('CAT_ADDED', 'rex_a62_metainfo_form');
rex_register_extension('CAT_UPDATED', 'rex_a62_metainfo_form');

rex_register_extension('CAT_FORM_BUTTONS', 'rex_a62_metainfo_button');

function rex_a62_metainfo_button($params)
{
	global $REX, $I18N;
	
  $s = '';
	$restrictionsCondition = '';
	if(isset($params['id']) && $params['id'] != '')
	{
    $OOCat = OOCategory::getCategoryById($params['id']);
    
    // Alle Metafelder des Pfades sind erlaubt
    foreach(explode('|', $OOCat->getPath()) as $pathElement)
    {
      if($pathElement != '')
      {
        $s .= ' OR `p`.`restrictions` LIKE "%|'. $pathElement .'|%"';
      }
    }
    
    // Auch die Kategorie selbst kann Metafelder haben
    $s .= ' OR `p`.`restrictions` LIKE "%|'. $params['id'] .'|%"';
	}
  $restrictionsCondition = 'AND (`p`.`restrictions` = ""'. $s .')';

	
	$fields = _rex_a62_metainfo_sqlfields('cat_', $restrictionsCondition);
	if ($fields->getRows() >= 1)
  {
  	$return = '<p class="rex-button-add"><script type="text/javascript"><!--

  function rex_metainfo_toggle()
  {
  	jQuery("#rex-form-structure-category .rex-metainfo-cat").toggle();
		metacat = jQuery("#rex-i-meta-category");
		if(metacat.hasClass("rex-i-generic-open"))
		{
			metacat.removeClass("rex-i-generic-open");
			metacat.addClass("rex-i-generic-close");
		}
		else 
		{
			metacat.removeClass("rex-i-generic-close");
			metacat.addClass("rex-i-generic-open");
		}
  }

  //--></script><a id="rex-i-meta-category" class="rex-i-generic-open" href="javascript:rex_metainfo_toggle();">'. $I18N->msg('minfo_edit_metadata') .'</a></p>';

	   return $params['subject'] . $return;
  }

  return $params['subject'];
}

/**
 * Callback, dass ein Formular item formatiert
 */
function rex_a62_metainfo_form_item($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
{
  global $REX;

  $add_td = '';
  $class_td = '';
  $class_tr = '';
  if ($REX['USER']->hasPerm('advancedMode[]'))
    $add_td = '<td></td>';
  
  $element = $field;
  if ($labelIt)
  {
    $element = '
  	   <'.$tag.$tag_attr.'>
  	     <label for="'. $id .'">'. $label .'</label>
  	     '.$field.'
  	   </'.$tag.'>';
  }
  
  if ($typeLabel == 'legend')
  {
  	$element = '<p class="rex-form-legend">'. $label .'</p>';
    $class_td = ' class="rex-colored"';
    $class_tr .= ' rex-metainfo-cat-b';
  }
  
  $s = '
  <tr class="rex-table-row-activ rex-metainfo-cat'. $class_tr .'" style="display:none;">
  	<td></td>
  	'.$add_td.'
  	<td colspan="5"'.$class_td.'>
   	  <div class="rex-form-row">
  	    '.$element.'
  	  </div>
    </td>
  </tr>';

  return $s;
}

/**
 * Erweitert das Meta-Formular um die neuen Meta-Felder
 */
function rex_a62_metainfo_form($params)
{
  if(isset($params['category']))
  {
    $params['activeItem'] = $params['category'];

    // Hier die category_id setzen, damit beim klick auf den REX_LINK_BUTTON der Medienpool in der aktuellen Kategorie startet
    $params['activeItem']->setValue('category_id', $params['id']);
  }

  $result = _rex_a62_metainfo_form('cat_', $params, '_rex_a62_metainfo_cat_handleSave');

  // Bei CAT_ADDED und CAT_UPDATED nur speichern und kein Formular zurückgeben
  if($params['extension_point'] == 'CAT_UPDATED' || $params['extension_point'] == 'CAT_ADDED')
    return $params['subject'];
  else
    return $params['subject'] . $result;
}