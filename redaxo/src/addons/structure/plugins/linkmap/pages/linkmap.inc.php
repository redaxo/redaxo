<?php

// ------- Default Values

$HTMLArea = rex_request('HTMLArea', 'string');
$opener_input_field = rex_request('opener_input_field', 'string');
$opener_input_field_name = rex_request('opener_input_field_name', 'string');
$category_id = rex_request('category_id', 'rex-category-id');
$clang = rex_request('clang', 'rex-clang-id');


$context = new rex_context(array(
  'page' => rex::getProperty('page'),
  'HTMLArea' => $HTMLArea,
  'opener_input_field' => $opener_input_field,
  'opener_input_field_name' => $opener_input_field_name,
  'category_id' =>$category_id,
  'clang' => $clang
));

// ------- Build JS Functions

$func_body = '';
if ($HTMLArea != '')
{
  if ($HTMLArea == 'TINY')
  {
    $func_body = 'window.opener.tinyMCE.insertLink(link);';
  }
  else
  {
    $func_body = 'window.opener.'.$HTMLArea.'.surroundHTML("<a href="+link+">","</a>");';
  }
}

if ($opener_input_field != '' && $opener_input_field_name == '')
{
  $opener_input_field_name = $opener_input_field.'_NAME';
}
if($opener_input_field=="TINY"){
	$func_body .= 'window.opener.insertLink(link,name);
	               self.close();';
}
else if (substr($opener_input_field,0,13)=="REX_LINKLIST_")
{
$id = substr($opener_input_field,13,strlen($opener_input_field));
$func_body .= 'var linklist = "REX_LINKLIST_SELECT_'. $id .'";
               var linkid = link.replace("redaxo://","");
			   var source = opener.document.getElementById(linklist);
			   var sourcelength = source.options.length;

               option = opener.document.createElement("OPTION");
               option.text = name;
               option.value = linkid;

			   source.options.add(option, sourcelength);
			   opener.writeREXLinklist('. $id .');';
}
else {
$func_body .= 'var linkid = link.replace("redaxo://","");
               window.opener.document.getElementById("'. $opener_input_field .'").value = linkid;
               window.opener.document.getElementById("'. $opener_input_field_name .'").value = name;
               self.close();';
}


// ------------------------ Print JS Functions

?>
<script type="text/javascript">
  function insertLink(link,name){
    <?php echo $func_body. "\n" ?>
  }
</script>

<?php

$navi_path = '<ul id="rex-navi-path">';


$isRoot = $category_id === 0;
$category = rex_ooCategory::getCategoryById($category_id);
$link = $context->getUrl(array('category_id' => 0));

$navi_path .= '<li>'.rex_i18n::msg('path').' </li>';
$navi_path .= '<li class="rex-navi-first">: <a href="'.$link.'">Homepage</a> </li>';

$tree = array();

if ($category)
{
  foreach($category->getParentTree() as $cat)
  {
    $tree[] = $cat->getId();
    
    $link = $context->getUrl(array('category_id' => $cat->getId()));
    $navi_path .= '<li> : <a href="'. $link .'">'.htmlspecialchars($cat->getName()).'</a></li>';
  }
}
$navi_path .= '</ul>';

//rex_title(rex::getProperty('servername'), 'Linkmap');
rex_view::title('Linkmap', $navi_path);

?>

<div id="rex-linkmap">
	<div class="rex-area-col-2">
		<div class="rex-area-col-a">
			<h3 class="rex-hl2"><?php echo rex_i18n::msg('lmap_categories'); ?></h3>
			<div class="rex-area-content">
			<?php
      $categoryTree = new rex_linkmap_categoryTree($context);
			echo $categoryTree->getTree($category_id);
			?>
			</div>
		</div>

		<div class="rex-area-col-b">
			<h3 class="rex-hl2"><?php echo rex_i18n::msg('lmap_articles'); ?></h3>
			<div class="rex-area-content">
			<?php
      $articleList = new rex_linkmap_articleList($context);
      echo $articleList->getList($category_id);
			?>
			</div>
		</div>
  </div>
</div>