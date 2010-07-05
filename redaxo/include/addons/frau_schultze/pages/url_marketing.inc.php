<?php

/**
 * Url Marketing Addon - "Frau Schultze"
 *
 * @author kai.kristinus[at]yakamara[dot]de - Kai Kristinus
 * @author <a href="http://www.yakamara.de/">yakamara</a>
 * 
 * @author mail[at]blumbeet[dot]com Thomas Blum
 * @author <a href="http://www.blumbeet.com/">blumbeet - web.studio</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */
		
$table = $REX['TABLE_PREFIX'].'a724_frau_schultze';

if ($func == 'status')
{
	if ($oid > 0)
	{
		$sql = new rex_sql();
		$sql->setQuery('SELECT status FROM '.$table.' WHERE pid = "'.$oid.'" LIMIT 2');
		
		if ($sql->getRows() == 1)
		{
			$sqlu = new rex_sql();
			$sqlu->setTable($table);
			$sqlu->setWhere('pid = "'.$oid.'"');
			
			if ($sql->getValue('status') == '1')
				$sqlu->setValue('status', '0');
			else
				$sqlu->setValue('status', '1');
				
			$sqlu->update();
			
			// XML Dateien neu erstellen
			// ep_generateAllFlashXML();
		}
	}	
	
	$func = '';
}

 

if ($func == '')
{

  $query = 'SELECT pid, name, url, article_id, clang, type, status FROM '.$table.' WHERE url_table_parameters = "" ORDER BY name';

	$list = rex_list::factory($query, 30, 'urls');
//	$list->debug = true;
	$list->setNoRowsMessage($I18N->msg('b_no_results', $I18N->msg('a724_subpage_marketing')));
  $list->setCaption('Urls');
  $list->addTableAttribute('summary', $I18N->msg('a724_subpage_marketing'));

	$list->addTableColumnGroup(array(40, '*', 100, 150, 80, 76, 76));
	
	$imgHeader = '<a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('func' => 'add')) .'"><span class="rex-i-element-text">'. $I18N->msg('b_add_entry', $I18N->msg('b1_room')) .'</span></a>';
	$list->addColumn($imgHeader, '###pid###', 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));
		
  $list->removeColumn('pid');
  $list->removeColumn('clang');
  $list->setColumnLabel('name', $I18N->msg('a724_enter_url'));  
  $list->setColumnLabel('url', $I18N->msg('a724_manual_url'));  
  $list->setColumnLabel('type', $I18N->msg('a724_type'));  


  
  
  $list->setColumnLabel('article_id', $I18N->msg('a724_article_url'));
  $list->setColumnFormat('article_id', 'custom', 
    create_function( 
      '$params', 
      'global $REX, $I18N;
       $list = $params["list"]; 
       $url = rex_getUrl($list->getValue("article_id"), $list->getValue("clang"));
       $str = "<a href=\"".$url."\">".$url."</a> [".$list->getValue("article_id")."|".$list->getValue("clang")."]";
       return $str;' 
    ) 
  ); 
  
  
  $list->setColumnLabel('status', $I18N->msg('b_function'));
  $list->setColumnParams('status', array('func'=>'status', 'oid'=>'###pid###'));
  $list->setColumnLayout('status', array('<th colspan="2">###VALUE###</th>','<td style="text-align:center;">###VALUE###</td>'));
  $list->setColumnFormat('status', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       if ($list->getValue("status") == 1) 
         $str = $list->getColumnLink("status","<span class=\"rex-online\">".$I18N->msg("b_status_online")."</span>"); 
       else 
         $str = $list->getColumnLink("status","<span class=\"rex-offline\">".$I18N->msg("b_status_offline")."</span>"); 
       return $str;' 
    ) 
  ); 
  
	$list->addColumn('Funktion', 'editieren', -1, array("",'<td style="text-align:center;">###VALUE###</td>'));
	$list->setColumnParams('Funktion', array('func' => 'edit', 'oid' => '###pid###'));
  
  
	$list->show();

}


if ($func == 'add' || $func == 'edit')
{
  
  $legend = $func == 'edit' ? $I18N->msg('b_edit') : $I18N->msg('b_add');
	
	$form = new rex_form($table, $I18N->msg('a724_subpage_marketing').' '.$legend, 'pid='.$oid, 'post', false);
//	$form->debug = true;
	
	if($func == 'edit')
	{
		$form->addParam('oid', $oid);
	}
	
	
  $field =& $form->addTextField('name');
  $field->setLabel($I18N->msg('a724_enter_url'));
  $field->setNotice($I18N->msg('a724_enter_url_notice'));
  
  
  $form->addFieldset($I18N->msg('a724_subpage_marketing_legend_v1'));
  
  $field =& $form->addLinkmapField('article_id');
  $field->setLabel($I18N->msg('a724_article'));
	
	$field =& $form->addSelectField('clang');
	$field->setLabel($I18N->msg('a724_language'));
	$field->setAttribute('style', 'width: 266px;');
	$select =& $field->getSelect();
	$select->setSize(1);
	
	foreach ($REX['CLANG'] as $key => $value)
	{
		$select->addOption($value, $key);
	}
	
	$field =& $form->addSelectField('redirect');
	$field->setLabel($I18N->msg('a724_what_should_happen'));
	$field->setAttribute('style', 'width: 266px;');
	$select =& $field->getSelect();
	$select->setSize(1);
	$select->addOption($I18N->msg('a724_what_should_happen_0'), '0');
	$select->addOption($I18N->msg('a724_what_should_happen_1'), '1');
	
  
  $form->addFieldset($I18N->msg('a724_subpage_marketing_legend_v2'));
  
  $field =& $form->addTextField('url');
  $field->setLabel($I18N->msg('a724_manual_url'));
  $field->setNotice($I18N->msg('a724_manual_url_notice'));
  
  
  $form->addFieldset($I18N->msg('a724_subpage_marketing_legend_status'));
		
	$field =& $form->addSelectField('type');
	$field->setLabel($I18N->msg('a724_http_type'));
	$field->setAttribute('style', 'width: 266px;');
	$select =& $field->getSelect();
	$select->setSize(1);
	$select->addOption($I18N->msg('a724_http_type_301'), '301');
	$select->addOption($I18N->msg('a724_http_type_303'), '303');
	
	
	$form->show();
		
	
	
}
?>