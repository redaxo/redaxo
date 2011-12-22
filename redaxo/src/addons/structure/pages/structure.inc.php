<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// basic request vars
$category_id = rex_request('category_id', 'rex-category-id');
$article_id  = rex_request('article_id',  'rex-article-id');
$clang       = rex_request('clang',       'rex-clang-id');
$ctype       = rex_request('ctype',       'rex-ctype-id');

// additional request vars
$artstart    = rex_request('artstart',    'int');
$catstart    = rex_request('catstart',    'int');
$edit_id     = rex_request('edit_id',     'rex-category-id');
$function    = rex_request('function',    'string');

$info = '';
$warning = '';





// --------------------------------------------- Mountpoints

$mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
if(count($mountpoints)==1 && $category_id == 0)
{
  // Nur ein Mointpoint -> Sprung in die Kategory
  $category_id = current($mountpoints);
}

// --------------------------------------------- Rechte prüfen
$KATPERM = rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id);

require dirname(__FILE__) .'/../functions/function_rex_category.inc.php';



// --------------------------------------------- TITLE

echo rex_view::title(rex_i18n::msg('title_structure'), $KATout);

$sprachen_add = '&amp;category_id='. $category_id;
require dirname(__FILE__) .'/../functions/function_rex_languages.inc.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();

$context = new rex_context(array(
  'page' => 'structure',
  'category_id' => $category_id,
  'article_id' => $article_id,
  'clang' => $clang,
  'ctype' => $ctype,
  'artstart' => $artstart,
  'catstart' => $catstart,
));

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$cat_name = 'Homepage';
$category = rex_ooCategory::getCategoryById($category_id, $clang);
if($category)
  $cat_name = $category->getName();

$add_category = '';
if ($KATPERM && !rex::getUser()->hasPerm('editContentOnly[]'))
{
  $add_category = '<a class="rex-i-element rex-i-category-add" href="'. $context->getUrl(array('function' => 'add_cat')) .'"'. rex::getAccesskey(rex_i18n::msg('add_category'), 'add') .'><span class="rex-i-element-text">'.rex_i18n::msg("add_category").'</span></a>';
}

$add_header = '';
$add_col = '';
$data_colspan = 4;
if (rex::getUser()->hasPerm('advancedMode[]'))
{
  $add_header = '<th class="rex-small">'.rex_i18n::msg('header_id').'</th>';
  $add_col = '<col width="40" />';
  $data_colspan = 5;
}

// --------------------- Extension Point
echo rex_extension::registerPoint('PAGE_STRUCTURE_HEADER', '',
  array(
    'category_id' => $category_id,
    'clang' => $clang
  )
);


// --------------------- COUNT CATEGORY ROWS

$KAT = rex_sql::factory();
// $KAT->debugsql = true;
if(count($mountpoints)>0 && $category_id == 0)
{
  $re_ids = implode(',', $mountpoints);
  $KAT->setQuery(
  	'SELECT COUNT(*) as rowCount, MAX(catprior) as maxCatPrior FROM '.rex::getTable('article').' WHERE id IN (:mntPoints) AND startpage=1 AND clang=:clang',
    array(':mntPoints' => $re_ids, ':clang' => $clang)
  );
}else
{
  $KAT->setQuery(
  	'SELECT COUNT(*) as rowCount, MAX(catprior) as maxCatPrior FROM '.rex::getTable('article').' WHERE re_id=:category AND startpage=1 AND clang=:clang',
    array(':category' => $category_id, ':clang' => $clang)
  );
}

$maxCatPrior = $KAT->getValue('maxCatPrior');

// --------------------- ADD PAGINATION

// FIXME add a realsitic rowsPerPage value
$catPager = new rex_pager($KAT->getValue('rowCount'), 3, 'catstart');
$catFragment = new rex_fragment();
$catFragment->setVar('urlprovider', $context);
$catFragment->setVar('pager', $catPager);
echo $catFragment->parse('pagination');

// --------------------- GET THE DATA

if(count($mountpoints)>0 && $category_id == 0)
{
  // named-parameters are not supported in LIMIT clause!
  $re_ids = implode(',', $mountpoints);
  $KAT->setQuery(
  	'SELECT * FROM '.rex::getTable('article').' WHERE id IN (:mntPoints) AND startpage=1 AND clang=:clang ORDER BY catname LIMIT '. $catPager->getCursor() .','. $catPager->getRowsPerPage(),
    array(':mntPoints' => $re_ids, ':clang' => $clang)
  );
}else
{
  // named-parameters are not supported in LIMIT clause!
  $KAT->setQuery(
  	'SELECT * FROM '.rex::getTable('article').' WHERE re_id=:category AND startpage=1 AND clang=:clang ORDER BY catprior LIMIT '. $catPager->getCursor() .','. $catPager->getRowsPerPage(),
    array(':category' => $category_id, ':clang' => $clang)
  );
}


echo '
<!-- *** OUTPUT CATEGORIES - START *** -->';

echo '<div class="rex-block rex-structure-category">';

// ---------- INLINE THE EDIT/ADD FORM
if($function == 'add_cat' || $function == 'edit_cat')
{

  $legend = rex_i18n::msg('add_category');
  if ($function == 'edit_cat')
    $legend = rex_i18n::msg('edit_category');

  echo '
  <div class="rex-form" id="rex-form-structure-category">
  <form action="index.php" method="post">
    <fieldset>
      <legend><span>'.htmlspecialchars($legend) .'</span></legend>';

  $params = array();
  $params['catstart'] = $catstart;
  if ($function == 'edit_cat')
  {
    $params['edit_id'] = $edit_id;
  }

	echo $context->getHiddenInputFields($params);
}


// --------------------- PRINT CATS/SUBCATS

echo '
      <table class="rex-table" summary="'. htmlspecialchars(rex_i18n::msg('structure_categories_summary', $cat_name)) .'">
        <caption>'. htmlspecialchars(rex_i18n::msg('structure_categories_caption', $cat_name)) .'</caption>
        <colgroup>
          <col width="40" />
          '. $add_col .'
          <col width="*" />
          <col width="40" />
          <col width="51" />
          <col width="50" />
          <col width="50" />
        </colgroup>
        <thead>
          <tr>
            <th class="rex-icon">'. $add_category .'</th>
            '. $add_header .'
            <th>'.rex_i18n::msg('header_category').'</th>
            <th>'.rex_i18n::msg('header_priority').'</th>
            <th colspan="3">'.rex_i18n::msg('header_status').'</th>
          </tr>
        </thead>
        <tbody>';
if ($category_id != 0 && ($category = rex_ooCategory::getCategoryById($category_id)))
{
  echo '<tr>
          <td class="rex-icon">&nbsp;</td>';
  if (rex::getUser()->hasPerm('advancedMode[]'))
  {
    echo '<td class="rex-small">-</td>';
  }


  echo '<td><a href="'. $context->getUrl(array('category_id' => $category->getParentId())) .'">..</a></td>';
  echo '<td>&nbsp;</td>';
  echo '<td colspan="3">&nbsp;</td>';
  echo '</tr>';

}

// --------------------- KATEGORIE ADD FORM

if ($function == 'add_cat' && $KATPERM && !rex::getUser()->hasPerm('editContentOnly[]'))
{
  $add_td = '';
  if (rex::getUser()->hasPerm('advancedMode[]'))
  {
    $add_td = '<td class="rex-small">-</td>';
  }

  $meta_buttons = rex_extension::registerPoint('CAT_FORM_BUTTONS', "" );
  $add_buttons = '
  	<input type="hidden" name="rex-api-call" value="category_add" />
  	<input type="hidden" name="parent-category-id" value="'. $category_id .'" />
  	<input type="submit" class="rex-form-submit" name="category-add-button" value="'. rex_i18n::msg('add_category') .'"'. rex::getAccesskey(rex_i18n::msg('add_category'), 'save') .' />';

  $class = 'rex-table-row-active';
  if($meta_buttons != "")
    $class .= ' rex-has-metainfo';

  echo '
        <tr class="'. $class .'">
          <td class="rex-icon"><span class="rex-i-element rex-i-category"><span class="rex-i-element-text">'. rex_i18n::msg('add_category') .'</span></span></td>
          '. $add_td .'
          <td><input class="rex-form-text" type="text" id="rex-form-field-name" name="category-name" />'. $meta_buttons .'</td>
          <td><input class="rex-form-text" type="text" id="rex-form-field-prior" name="category-position" value="'.($maxCatPrior+1).'" /></td>
          <td colspan="3">'. $add_buttons .'</td>
        </tr>';

  // ----- EXTENSION POINT
  echo rex_extension::registerPoint('CAT_FORM_ADD', '', array (
      'id' => $category_id,
      'clang' => $clang,
      'data_colspan' => ($data_colspan+1),
    ));
}





// --------------------- KATEGORIE LIST

for ($i = 0; $i < $KAT->getRows(); $i++)
{
  $i_category_id = $KAT->getValue('id');

  $kat_link = $context->getUrl(array('category_id' => $i_category_id));
  $kat_icon_td = '<td class="rex-icon"><a class="rex-i-element rex-i-category" href="'. $kat_link .'"><span class="rex-i-element-text">'. htmlspecialchars($KAT->getValue("catname")). '</span></a></td>';

  $kat_status = $catStatusTypes[$KAT->getValue('status')][0];
  $status_class = $catStatusTypes[$KAT->getValue('status')][1];

  if ($KATPERM)
  {
    if (rex::getUser()->isAdmin() || $KATPERM && rex::getUser()->hasPerm('publishCategory[]'))
    {
      $kat_status = '<a href="'. $context->getUrl(array('category-id' => $i_category_id, 'rex-api-call' => 'category_status', 'catstart' => $catstart)) .'" class="rex-api-get '. $status_class .'">'. $kat_status .'</a>';
    }
    else
    {
      $kat_status = '<span class="rex-strike '. $status_class .'">'. $kat_status .'</span>';
    }

    if (isset ($edit_id) && $edit_id == $i_category_id && $function == 'edit_cat')
    {
      // --------------------- KATEGORIE EDIT FORM
      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
      {
        $add_td = '<td class="rex-small">'. $i_category_id .'</td>';
      }

      // ----- EXTENSION POINT
      $meta_buttons = rex_extension::registerPoint('CAT_FORM_BUTTONS', '', array(
        'id' => $edit_id,
        'clang' => $clang,
      ));

      $add_buttons = '
    	<input type="hidden" name="rex-api-call" value="category_edit" />
    	<input type="hidden" name="category-id" value="'. $edit_id .'" />
      <input type="submit" class="rex-form-submit" name="category-edit-button" value="'. rex_i18n::msg('save_category'). '"'. rex::getAccesskey(rex_i18n::msg('save_category'), 'save') .' />';

      $class = 'rex-table-row-active';
      if($meta_buttons != "")
        $class .= ' rex-has-metainfo';

      echo '
        <tr class="'. $class .'">
          '. $kat_icon_td .'
          '. $add_td .'
          <td><input type="text" class="rex-form-text" id="rex-form-field-name" name="category-name" value="'. htmlspecialchars($KAT->getValue("catname")). '" />'. $meta_buttons .'</td>
          <td><input type="text" class="rex-form-text" id="rex-form-field-prior" name="category-position" value="'. htmlspecialchars($KAT->getValue("catprior")) .'" /></td>
          <td colspan="3">'. $add_buttons .'</td>
        </tr>';

      // ----- EXTENSION POINT
      echo rex_extension::registerPoint('CAT_FORM_EDIT', '', array (
        'id' => $edit_id,
        'clang' => $clang,
        'category' => $KAT,
        'catname' => $KAT->getValue('catname'),
        'catprior' => $KAT->getValue('catprior'),
        'data_colspan' => ($data_colspan+1),
      ));

    }
    else
    {
      // --------------------- KATEGORIE WITH WRITE

      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
      {
        $add_td = '<td class="rex-small">'. $i_category_id .'</td>';
      }

      if (!rex::getUser()->hasPerm('editContentOnly[]'))
      {
        $category_delete = '<a href="'. $context->getUrl(array('category-id' => $i_category_id, 'rex-api-call' => 'category_delete', 'catstart' => $catstart)) .'" class="rex-api-get" onclick="return confirm(\''.rex_i18n::msg('delete').' ?\')">'.rex_i18n::msg('delete').'</a>';
      }
      else
      {
        $category_delete = '<span class="rex-strike">'. rex_i18n::msg('delete') .'</span>';
      }

      echo '
        <tr>
          '. $kat_icon_td .'
          '. $add_td .'
          <td><a href="'. $kat_link .'">'. htmlspecialchars($KAT->getValue("catname")) .'</a></td>
          <td>'. htmlspecialchars($KAT->getValue("catprior")) .'</td>
          <td><a href="'. $context->getUrl(array('edit_id' => $i_category_id, 'function' => 'edit_cat', 'catstart' => $catstart)) .'">'. rex_i18n::msg('change') .'</a></td>
          <td>'. $category_delete .'</td>
          <td>'. $kat_status .'</td>
        </tr>';
    }

  }
  elseif (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($i_category_id))
  {
      // --------------------- KATEGORIE WITH READ
      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
      {
        $add_td = '<td class="rex-small">'. $i_category_id .'</td>';
      }

      echo '
        <tr>
          '. $kat_icon_td .'
          '. $add_td .'
          <td><a href="'. $kat_link .'">'.$KAT->getValue("catname").'</a></td>
          <td>'.htmlspecialchars($KAT->getValue("catprior")).'</td>
          <td><span class="rex-strike">'. rex_i18n::msg('change') .'</span></td>
          <td><span class="rex-strike">'. rex_i18n::msg('delete') .'</span></td>
          <td><span class="rex-strike '. $status_class .'">'. $kat_status .'</span></td>
        </tr>';
  }

  $KAT->next();
}

echo '
      </tbody>
    </table>';

if($function == 'add_cat' || $function == 'edit_cat')
{
  echo '
    <script type="text/javascript">
      <!--
      jQuery(function($){
        $("#rex-form-field-name").focus();
      });
      //-->
    </script>
  </fieldset>
</form>
</div>';
}

echo '</div>';

echo '
<!-- *** OUTPUT CATEGORIES - END *** -->
';

// --------------------------------------------- ARTIKEL LISTE

echo '
<!-- *** OUTPUT ARTICLES - START *** -->';

echo '<div class="rex-block rex-structure-article">';

// --------------------- READ TEMPLATES

if ($category_id > 0 || ($category_id == 0 && !rex::getUser()->getComplexPerm('structure')->hasMountpoints()))
{

  $template_select = new rex_select;
  $template_select->setName('template_id');
  $template_select->setId('rex-form-template');
  $template_select->setSize(1);

  $templates = rex_ooCategory::getTemplates($category_id);
  if(count($templates)>0)
  {
    foreach($templates as $t_id => $t_name)
    {
      $template_select->addOption(rex_i18n::translate($t_name, null, false), $t_id);
      $TEMPLATE_NAME[$t_id] = rex_i18n::translate($t_name);
    }
  }else
  {
    $template_select->addOption(rex_i18n::msg('option_no_template'), '0');
    $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');
  }

  // --------------------- ARTIKEL LIST
  $art_add_link = '';
  if ($KATPERM && !rex::getUser()->hasPerm('editContentOnly[]'))
    $art_add_link = '<a class="rex-i-element rex-i-article-add" href="'. $context->getUrl(array('function' => 'add_art')) .'"'. rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2') .'><span class="rex-i-element-text">'. rex_i18n::msg('article_add') .'</span></a>';

  $add_head = '';
  $add_col  = '';
  if (rex::getUser()->hasPerm('advancedMode[]'))
  {
    $add_head = '<th class="rex-small">'. rex_i18n::msg('header_id') .'</th>';
    $add_col  = '<col width="40" />';
  }

  // ---------- COUNT DATA
  $sql = rex_sql::factory();
  // $sql->debugsql = true;
  $sql->setQuery(
  	'SELECT COUNT(*) as artCount, MAX(prior) as maxArtPrior FROM '.rex::getTablePrefix().'article
     WHERE
       ((re_id=:categoryid AND startpage=0) OR (id=:categoryid AND startpage=1))
       AND clang=:clang',
    array(':categoryid' => $category_id, 'clang' => $clang)
  );
  
  $maxArtPrior = $sql->getValue('maxArtPrior');

  // --------------------- ADD PAGINATION

  // FIXME add a realsitic rowsPerPage value
  $artPager = new rex_pager($sql->getValue('artCount'), 3, 'artstart');
  $artFragment = new rex_fragment();
  $artFragment->setVar('urlprovider', $context);
  $artFragment->setVar('pager', $artPager);
  echo $artFragment->parse('pagination');

  // ---------- READ DATA
  // named-parameters are not supported in LIMIT clause!
  $sql->setQuery(
  	'SELECT * FROM '.rex::getTable('article').'
     WHERE
       ((re_id=:categoryid AND startpage=0) OR (id=:categoryid AND startpage=1))
       AND clang=:clang
     ORDER BY
       prior, name
     LIMIT '. $artPager->getCursor() .','. $artPager->getRowsPerPage(), 
    array(':categoryid' => $category_id, 'clang' => $clang)
  );


  // ---------- INLINE THE EDIT/ADD FORM
  if($function == 'add_art' || $function == 'edit_art')
  {

    $legend = rex_i18n::msg('article_add');
    if ($function == 'edit_art')
      $legend = rex_i18n::msg('article_edit');

    echo '
    <div class="rex-form" id="rex-form-structure-article">
    <form action="index.php" method="post">
      <fieldset>
        <legend><span>'.htmlspecialchars($legend) .'</span></legend>';

    $params = array();
    $params['artstart'] = $artstart;
    echo $context->getHiddenInputFields($params);
  }

  // ----------- PRINT OUT THE ARTICLES

  echo '
      <table class="rex-table" summary="'. htmlspecialchars(rex_i18n::msg('structure_articles_summary', $cat_name)) .'">
        <caption>'. htmlspecialchars(rex_i18n::msg('structure_articles_caption', $cat_name)).'</caption>
        <colgroup>
          <col width="40" />
          '. $add_col .'
          <col width="*" />
          <col width="40" />
          <col width="200" />
          <col width="115" />
          <col width="51" />
          <col width="50" />
          <col width="50" />
        </colgroup>
        <thead>
          <tr>
            <th class="rex-icon">'. $art_add_link .'</th>
            '. $add_head .'
            <th>'.rex_i18n::msg('header_article_name').'</th>
            <th>'.rex_i18n::msg('header_priority').'</th>
            <th>'.rex_i18n::msg('header_template').'</th>
            <th>'.rex_i18n::msg('header_date').'</th>
            <th colspan="3">'.rex_i18n::msg('header_status').'</th>
          </tr>
        </thead>
        ';

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if($sql->getRows() > 0 || $function == 'add_art')
  {
    echo '<tbody>
          ';
  }

  // --------------------- ARTIKEL ADD FORM
  if ($function == 'add_art' && $KATPERM && !rex::getUser()->hasPerm('editContentOnly[]'))
  {
    $defaultTemplateId = rex::getProperty('default_template_id');
    if($defaultTemplateId > 0 && isset($TEMPLATE_NAME[$defaultTemplateId]))
    {
      $template_select->setSelected($defaultTemplateId);

    }else
    {
      // template_id vom Startartikel erben
      $sql2 = rex_sql::factory();
      $sql2->setQuery('SELECT template_id FROM '.rex::getTablePrefix().'article WHERE id='. $category_id .' AND clang='. $clang .' AND startpage=1');
      if ($sql2->getRows() == 1)
        $template_select->setSelected($sql2->getValue('template_id'));
    }

    $add_td = '
    	<input type="hidden" name="rex-api-call" value="article_add" />
    ';

    if (rex::getUser()->hasPerm('advancedMode[]'))
      $add_td .= '<td class="rex-small">-</td>';

      echo '<tr class="rex-table-row-active">
            <td class="rex-icon"><span class="rex-i-element rex-i-article"><span class="rex-i-element-text">'.rex_i18n::msg('article_add') .'</span></span></td>
            '. $add_td .'
            <td><input type="text" class="rex-form-text" id="rex-form-field-name" name="article-name" /></td>
            <td><input type="text" class="rex-form-text" id="rex-form-field-prior" name="article-position" value="'.($maxArtPrior+1).'" /></td>
            <td>'. $template_select->get() .'</td>
            <td>'. rex_formatter :: format(time(), 'strftime', 'date') .'</td>
            <td colspan="3"><input type="submit" class="rex-form-submit" name="artadd_function" value="'.rex_i18n::msg('article_add') .'"'. rex::getAccesskey(rex_i18n::msg('article_add'), 'save') .' /></td>
          </tr>
          ';
  }

  // --------------------- ARTIKEL LIST

  for ($i = 0; $i < $sql->getRows(); $i++)
  {

    if ($sql->getValue('startpage') == 1)
      $class = 'rex-i-article-startpage';
    else
      $class = 'rex-i-article';

    // --------------------- ARTIKEL EDIT FORM

    if ($function == 'edit_art' && $sql->getValue('id') == $article_id && $KATPERM)
    {
      $add_td = '
      	<input type="hidden" name="rex-api-call" value="article_edit" />
      ';

      if (rex::getUser()->hasPerm('advancedMode[]'))
        $add_td .= '<td class="rex-small">'. $sql->getValue("id") .'</td>';

      $template_select->setSelected($sql->getValue('template_id'));

      echo '<tr class="rex-table-row-active">
              <td class="rex-icon"><a class="rex-i-element '.$class.'" href="'. $context->getUrl(array('page' => 'content', 'article_id' => $sql->getValue('id'))) .'"><span class="rex-i-element-text">' .htmlspecialchars($sql->getValue("name")).'</span></a></td>
              '. $add_td .'
              <td><input type="text" class="rex-form-text" id="rex-form-field-name" name="article-name" value="' .htmlspecialchars($sql->getValue('name')).'" /></td>
              <td><input type="text" class="rex-form-text" id="rex-form-field-prior" name="article-position" value="'. htmlspecialchars($sql->getValue('prior')).'" /></td>
              <td>'. $template_select->get() .'</td>
              <td>'. rex_formatter :: format($sql->getValue('createdate'), 'strftime', 'date') .'</td>
              <td colspan="3"><input type="submit" class="rex-form-submit" name="artedit_function" value="'. rex_i18n::msg('article_save') .'"'. rex::getAccesskey(rex_i18n::msg('article_save'), 'save') .' /></td>
            </tr>
            ';

    }elseif ($KATPERM)
    {
      // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER

      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
        $add_td = '<td class="rex-small">'. $sql->getValue('id') .'</td>';

      $article_status = $artStatusTypes[$sql->getValue('status')][0];
      $article_class = $artStatusTypes[$sql->getValue('status')][1];

      $add_extra = '';
      if ($sql->getValue('startpage') == 1)
      {
        $add_extra = '<td><span class="rex-strike">'. rex_i18n::msg('delete') .'</span></td>
                      <td><span class="rex-strike '. $article_class .'">'. $article_status .'</span></td>';
      }else
      {
        if (rex::getUser()->isAdmin() || $KATPERM && rex::getUser()->hasPerm('publishArticle[]'))
          $article_status = '<a href="'. $context->getUrl(array('article_id' => $sql->getValue('id'), 'rex-api-call' => 'article_status', 'artstart' => $artstart)) .'" class="rex-api-get '. $article_class .'">'. $article_status .'</a>';
        else
          $article_status = '<span class="rex-strike '. $article_class .'">'. $article_status .'</span>';

        if (!rex::getUser()->hasPerm('editContentOnly[]'))
          $article_delete = '<a href="'. $context->getUrl(array('article_id' => $sql->getValue('id'), 'rex-api-call' => 'article_delete', 'artstart' => $artstart)) .'" class="rex-api-get" onclick="return confirm(\''.rex_i18n::msg('delete').' ?\')">'.rex_i18n::msg('delete').'</a>';
        else
          $article_delete = '<span class="rex-strike">'. rex_i18n::msg('delete') .'</span>';

        $add_extra = '<td>'. $article_delete .'</td>
                      <td>'. $article_status .'</td>';
      }

      $editModeUrl = $context->getUrl(array('page' => 'content', 'article_id' => $sql->getValue('id'), 'mode' => 'edit'));
      $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';

      echo '<tr>
              <td class="rex-icon"><a class="rex-i-element '.$class.'" href="'. $editModeUrl .'"><span class="rex-i-element-text">' .htmlspecialchars($sql->getValue('name')).'</span></a></td>
              '. $add_td .'
              <td><a href="'. $editModeUrl .'">'. htmlspecialchars($sql->getValue('name')) . '</a></td>
              <td>'. htmlspecialchars($sql->getValue('prior')) .'</td>
              <td>'. $tmpl .'</td>
              <td>'. rex_formatter :: format($sql->getValue('createdate'), 'strftime', 'date') .'</td>
              <td><a href="'. $context->getUrl(array('article_id' => $sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $artstart)) .'">'. rex_i18n::msg('change') .'</a></td>
              '. $add_extra .'
            </tr>
            ';

    }else
    {
      // --------------------- ARTIKEL NORMAL VIEW | NO EDIT NO ENTER

      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
        $add_td = '<td class="rex-small">'. $sql->getValue('id') .'</td>';

      $art_status = $artStatusTypes[$sql->getValue('status')][0];
      $art_status_class = $artStatusTypes[$sql->getValue('status')][1];
      $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';

      echo '<tr>
              <td class="rex-icon"><span class="rex-i-element '.$class.'"><span class="rex-i-element-text">' .htmlspecialchars($sql->getValue('name')).'"</span></span></td>
              '. $add_td .'
              <td>'. htmlspecialchars($sql->getValue('name')).'</td>
              <td>'. htmlspecialchars($sql->getValue('prior')).'</td>
              <td>'. $tmpl .'</td>
              <td>'. rex_formatter :: format($sql->getValue('createdate'), 'strftime', 'date') .'</td>
              <td><span class="rex-strike">'.rex_i18n::msg('change').'</span></td>
              <td><span class="rex-strike">'.rex_i18n::msg('delete').'</span></td>
              <td><span class="rex-strike '. $art_status_class .'">'. $art_status .'</span></td>
            </tr>
            ';
    }

    $sql->next();
  }

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if($sql->getRows() > 0 || $function == 'add_art')
  {
    echo '
        </tbody>';
  }

  echo '
      </table>';

  if($function == 'add_art' || $function == 'edit_art')
  {
    echo '
      <script type="text/javascript">
        <!--
        jQuery(function($){
          $("#rex-form-field-name").focus();
        });
        //-->
      </script>
    </fieldset>
  </form>
  </div>';
  }
}



echo '</div>';

echo '
<!-- *** OUTPUT ARTICLES - END *** -->
';