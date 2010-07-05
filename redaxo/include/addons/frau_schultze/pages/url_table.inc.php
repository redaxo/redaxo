<?php
/** 
 * Addon: 	by Thomas Blum
 * @author 	blumbeet - web.studio
 *					http://blumbeet.com
 */


rex_register_extension('REX_FORM_SAVED', 'a724_generatePathnamesFromTable');
/* EP exisitert noch nicht 
rex_register_extension('REX_FORM_DELETED', 'a724_deletePathnamesFromTable');
*/

$table = $REX['TABLE_PREFIX'].'a724_frau_schultze';

if ($func == '')
{

  $query = 'SELECT pid, article_id, url_table, url_table_parameters, name, clang FROM '.$table.' WHERE url_table != "" AND url_table_parameters != "" ORDER BY url_table, name';

	$list = rex_list::factory($query, 30, 'urls');
//	$list->debug = true;
	$list->setNoRowsMessage($I18N->msg('b_no_results', $I18N->msg('a724_subpage_url_table')));
  $list->setCaption($I18N->msg('a724_subpage_url_table'));
  $list->addTableAttribute('summary', $I18N->msg('a724_subpage_url_table'));

	$list->addTableColumnGroup(array(40, '*', 150, 80, 80, '153'));
	
	$imgHeader = '<a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('func' => 'add')) .'"><span class="rex-i-element-text">'. $I18N->msg('b_add_entry', $I18N->msg('b1_room')) .'</span></a>';
	$list->addColumn($imgHeader, '###pid###', 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));
		
  $list->removeColumn('pid');
  $list->removeColumn('clang');
  $list->removeColumn('name');
  $list->removeColumn('url_table_parameters');
  
  $list->setColumnLabel('article_id', $I18N->msg('a724_article'));
  $list->setColumnFormat('article_id', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       $a = new rex_article();
       $a->setArticleId($list->getValue("article_id"));
       $a->setClang($list->getValue("clang"));
       
       $str = $a->getValue("name");
       $str .= " [";
       $str .= "<a href=\"index.php?article_id=".$list->getValue("article_id")."&amp;clang=".$list->getValue("clang")."\">Backend</a>";
       $str .= " | ";
       $str .= "<a href=\"".rex_getUrl($list->getValue("article_id"), $list->getValue("clang"))."\">Frontend</a>";
       $str .= "]";
       return $str;'
    ) 
  );
  
  $list->setColumnLabel('url_table', $I18N->msg('a724_table'));

	$list->addColumn('url', '');
  $list->setColumnLabel('url', $I18N->msg('a724_url'));
  $list->setColumnFormat('url', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       
       $params = unserialize($list->getValue("url_table_parameters"));
       return $params[$list->getValue("url_table")][$list->getValue("url_table")."_name"];'
    ) 
  );

	$list->addColumn('id', '');
  $list->setColumnLabel('id', $I18N->msg('a724_id'));
  $list->setColumnFormat('id', 'custom', 
    create_function( 
      '$params', 
      'global $I18N;
       $list = $params["list"]; 
       
       $params = unserialize($list->getValue("url_table_parameters"));
       return $params[$list->getValue("url_table")][$list->getValue("url_table")."_id"];'
    ) 
  );

	$list->addColumn($I18N->msg('b_function'), $I18N->msg('b_edit'));
	$list->setColumnParams($I18N->msg('b_function'), array('func' => 'edit', 'oid' => '###pid###'));
  
	$list->show();

}


if ($func == 'add' || $func == 'edit')
{
  
  $legend = $func == 'edit' ? $I18N->msg('b_edit') : $I18N->msg('b_add');
	
	$form = new rex_form($table, 'Tabellen Url '.$legend, 'pid='.$oid, 'post', false);
//	$form->debug = true;
	
	if($func == 'edit')
	{
		$form->addParam('oid', $oid);
	}
  
  $field =& $form->addLinkmapField('article_id');
  $field->setLabel($I18N->msg('a724_article'));

	
	$field =& $form->addSelectField('url_table');
	$field->setLabel($I18N->msg('a724_table'));
	$field->setAttribute('onchange', 'url_table(this);');
	$field->setAttribute('style', 'width: '.$select_w4.'px;');
	$select =& $field->getSelect();
	$select->setSize(1);
  $select->addOption($I18N->msg('a724_no_table_selected'), '');
	
  $sql = new rex_sql();
  $tables = $sql->getArray("SHOW TABLES");
  
  $cols = array();
  foreach ($tables as $key => $value)
  {
    $select->addOption(current($value), current($value));
    
    $sqlf = new rex_sql();
//    $sqlf->setDebug(true);
    $sqlf->setQuery('SELECT * FROM '.current($value));
    $fieldnames = $sqlf->getFieldnames();
    
    foreach ($fieldnames as $fieldname)
    {
    	$fields[current($value)][] = $fieldname;
    }
  }
	
  $script = '
  <script type="text/javascript">
  <!--

  (function($) {
    var currentShown = null;
    $("#'. $field->getAttribute('id') .'").change(function(){
      if(currentShown) currentShown.hide();
      
      var effectParamsId = "#rex-"+ jQuery(this).val();
      currentShown = $(effectParamsId);
      currentShown.show();
    }).change();
  })(jQuery);
  
  //--></script>';
	
  $fieldContainer =& $form->addContainerField('url_table_parameters');
  $fieldContainer->setAttribute('style', 'display: none');
	$fieldContainer->setSuffix($script);
  
  
	
	if (count($fields > 0))
	{
		foreach ($fields as $table => $cols)
		{
			$group = $table;
			$type = 'select';
			$options = $cols;
			
			$name = $table.'_name';
			
			$f1 =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
			$f1->setLabel($I18N->msg('a724_url'));
			$f1->setAttribute('style', 'width: '.$select_w4.'px;');
			$f1->setNotice($I18N->msg('a724_table_url_notice_name'));
			$select =& $f1->getSelect();
			$select->setSize(1);
			$select->addOptions($options, true);
			
			
			$name = $table.'_id';
			
			$f2 =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
			$f2->setLabel($I18N->msg('a724_id'));
			$f2->setAttribute('style', 'width: '.$select_w4.'px;');
			$f2->setNotice($I18N->msg('a724_table_url_notice_id'));
			$select =& $f2->getSelect();
			$select->setSize(1);
			$select->addOptions($options, true);
			
		}
	}
	
	$form->show();

}

?>

<script type="text/javascript">
// <![CDATA[


	jQuery(document).ready(function($){
		
		function url_table(select)
		{
			url_table_show(select.value);
//			url_table_hiddenAll();
		}
		
		function url_table_show(select)
		{
			$(select + '_1').css('display', 'block');
			$(select + '_2').css('display', 'block');
		}
/*		
		function url_table_hiddenAll()
		{
			$('.addon-url-table-name').css('display', 'none');
			$('.addon-url-table-id').css('display', 'none');
		}
		url_table_hiddenAll();
*/	
	});
// ]]>
</script>