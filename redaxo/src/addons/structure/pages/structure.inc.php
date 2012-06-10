<?php

/**
 *
 * @package redaxo5
 */

// basic request vars
$category_id = rex_request('category_id', 'int');
$article_id  = rex_request('article_id',  'int');
$clang       = rex_request('clang',       'int');
$ctype       = rex_request('ctype',       'int');

// additional request vars
$artstart    = rex_request('artstart',    'int');
$catstart    = rex_request('catstart',    'int');
$edit_id     = rex_request('edit_id',     'int');
$function    = rex_request('function',    'string');

$info = '';
$warning = '';

$category_id = rex_category::getCategoryById($category_id) instanceof rex_category ? $category_id : 0;
$article_id = rex_article::getArticleById($article_id) instanceof rex_article ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex::getProperty('start_clang_id');



// --------------------------------------------- Mountpoints

$mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
if (count($mountpoints) == 1 && $category_id == 0) {
  // Nur ein Mointpoint -> Sprung in die Kategory
  $category_id = current($mountpoints);
}

// --------------------------------------------- Rechte prüfen
$KATPERM = rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id);
require dirname(__FILE__) . '/../functions/function_rex_category.inc.php';

$stop = false;
if (rex_clang::count() > 1) {
  if (!rex::getUser()->getComplexPerm('clang')->hasPerm($clang)) {
    $stop = true;
    foreach (rex_clang::getAllIds() as $key) {
      if (rex::getUser()->getComplexPerm('clang')->hasPerm($key)) {
        $clang = $key;
        $stop = false;
        break;
      }
    }

    if ($stop) {
      echo '
    <!-- *** OUTPUT OF CLANG-VALIDATE - START *** -->
          ' . rex_view::warning('You have no permission to this area') . '
    <!-- *** OUTPUT OF CLANG-VALIDATE - END *** -->
      ';
      exit;
    }
  }
} else {
  $clang = 0;
}


$context = new rex_context(array(
  'page' => 'structure',
  'category_id' => $category_id,
  'article_id' => $article_id,
  'clang' => $clang,
  'ctype' => $ctype,
));



// --------------------- Extension Point
echo rex_extension::registerPoint('PAGE_STRUCTURE_HEADER_PRE', '',
  array(
    'context' => $context
  )
);


// --------------------------------------------- TITLE

echo rex_view::title(rex_i18n::msg('title_structure'), $KATout);

$sprachen_add = '&amp;category_id=' . $category_id;
require dirname(__FILE__) . '/../functions/function_rex_languages.inc.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();





// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$cat_name = 'Homepage';
$category = rex_category::getCategoryById($category_id, $clang);
if ($category)
  $cat_name = $category->getName();

$add_category = '';
if ($KATPERM) {
  $add_category = '<a class="rex-ic-category rex-ic-add" href="' . $context->getUrl(array('function' => 'add_cat')) . '"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'add') . '>' . rex_i18n::msg('add_category') . '</a>';
}

$add_header = '';
$data_colspan = 4;
if (rex::getUser()->hasPerm('advancedMode[]')) {
  $add_header = '<th class="rex-id">' . rex_i18n::msg('header_id') . '</th>';
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
if (count($mountpoints) > 0 && $category_id == 0) {
  $re_id = implode(',', $mountpoints);
  $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $re_id . ') AND startpage=1 AND clang=' . $clang . ' ORDER BY catname');
} else {
  $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE re_id=' . $category_id . ' AND startpage=1 AND clang=' . $clang . ' ORDER BY catprior');
}

// --------------------- ADD PAGINATION

// FIXME add a realsitic rowsPerPage value
$catPager = new rex_pager($KAT->getValue('rowCount'), 30, 'catstart');
$catFragment = new rex_fragment();
$catFragment->setVar('urlprovider', $context);
$catFragment->setVar('pager', $catPager);
echo $catFragment->parse('pagination.tpl');

// --------------------- GET THE DATA

if (count($mountpoints) > 0 && $category_id == 0) {
  $re_id = implode(',', $mountpoints);
  $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $re_id . ') AND startpage=1 AND clang=' . $clang . ' ORDER BY catname LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
} else {
  $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE re_id=' . $category_id . ' AND startpage=1 AND clang=' . $clang . ' ORDER BY catprior LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
}



$echo = '';
// ---------- INLINE THE EDIT/ADD FORM
if ($function == 'add_cat' || $function == 'edit_cat') {

  $legend = rex_i18n::msg('add_category');
  if ($function == 'edit_cat')
    $legend = rex_i18n::msg('edit_category');

  $echo .= '
  <div class="rex-form" id="rex-form-structure-category">
  <form action="index.php" method="post">
    <fieldset>
      <legend><span>' . htmlspecialchars($legend) . '</span></legend>';

  $params = array();
  $params['catstart'] = $catstart;
  if ($function == 'edit_cat') {
    $params['edit_id'] = $edit_id;
  }

  $echo .= $context->getHiddenInputFields($params);
}


// --------------------- PRINT CATS/SUBCATS

$echo .= '
      <table id="rex-table-categories" class="rex-table" summary="' . htmlspecialchars(rex_i18n::msg('structure_categories_summary', $cat_name)) . '">
        <caption>' . htmlspecialchars(rex_i18n::msg('structure_categories_caption', $cat_name)) . '</caption>
        <thead>
          <tr>
            <th class="rex-icon">' . $add_category . '</th>
            ' . $add_header . '
            <th class="rex-name">' . rex_i18n::msg('header_category') . '</th>
            <th class="rex-prior">' . rex_i18n::msg('header_priority') . '</th>
            <th class="rex-function" colspan="3">' . rex_i18n::msg('header_status') . '</th>
          </tr>
        </thead>
        <tbody>';
if ($category_id != 0 && ($category = rex_category::getCategoryById($category_id))) {
  $echo .= '<tr>
          <td class="rex-icon">&nbsp;</td>';
  if (rex::getUser()->hasPerm('advancedMode[]')) {
    $echo .= '<td class="rex-small">-</td>';
  }


  $echo .= '<td class="rex-name"><a href="' . $context->getUrl(array('category_id' => $category->getParentId())) . '">..</a></td>';
  $echo .= '<td class="rex-prior">&nbsp;</td>';
  $echo .= '<td colspan="3">&nbsp;</td>';
  $echo .= '</tr>';

}

// --------------------- KATEGORIE ADD FORM

if ($function == 'add_cat' && $KATPERM) {
  $add_td = '';
  if (rex::getUser()->hasPerm('advancedMode[]')) {
    $add_td = '<td class="rex-small">-</td>';
  }

  $meta_buttons = rex_extension::registerPoint('CAT_FORM_BUTTONS', '', array(
    'id' => $category_id,
    'clang' => $clang
  ));
  $add_buttons = '
    <input type="hidden" name="rex-api-call" value="category_add" />
    <input type="hidden" name="parent-category-id" value="' . $category_id . '" />
    <input type="submit" class="rex-form-submit" name="category-add-button" value="' . rex_i18n::msg('add_category') . '"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'save') . ' />';

  $class = 'rex-table-row-active';
  if ($meta_buttons != '')
    $class .= ' rex-has-metainfo';

  $echo .= '
        <tr class="' . $class . '">
          <td class="rex-icon"><span class="rex-ic-category">' . rex_i18n::msg('add_category') . '</span></td>
          ' . $add_td . '
          <td class="rex-name"><input class="rex-form-text" type="text" id="rex-form-field-name" name="category-name" />' . $meta_buttons . '</td>
          <td class="rex-prior"><input class="rex-form-text" type="text" id="rex-form-field-prior" name="category-position" value="' . ($KAT->getRows() + 1) . '" /></td>
          <td colspan="3">' . $add_buttons . '</td>
        </tr>';

  // ----- EXTENSION POINT
  $echo .= rex_extension::registerPoint('CAT_FORM_ADD', '', array(
      'id' => $category_id,
      'clang' => $clang,
      'data_colspan' => ($data_colspan + 1),
    ));
}





// --------------------- KATEGORIE LIST

for ($i = 0; $i < $KAT->getRows(); $i++) {
  $i_category_id = $KAT->getValue('id');

  $kat_link = $context->getUrl(array('category_id' => $i_category_id));
  $kat_icon_td = '<td class="rex-icon"><a class="rex-ic-category" href="' . $kat_link . '">' . htmlspecialchars($KAT->getValue('catname')) . '</a></td>';

  $kat_status = $catStatusTypes[$KAT->getValue('status')][0];
  $status_class = $catStatusTypes[$KAT->getValue('status')][1];

  if ($KATPERM) {
    if ($KATPERM && rex::getUser()->hasPerm('publishCategory[]')) {
      $kat_status = '<a href="' . $context->getUrl(array('category-id' => $i_category_id, 'rex-api-call' => 'category_status', 'catstart' => $catstart)) . '" class="' . $status_class . '">' . $kat_status . '</a>';
    } else {
      $kat_status = '<span class="rex-strike ' . $status_class . '">' . $kat_status . '</span>';
    }

    if (isset ($edit_id) && $edit_id == $i_category_id && $function == 'edit_cat') {
      // --------------------- KATEGORIE EDIT FORM
      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]')) {
        $add_td = '<td class="rex-small">' . $i_category_id . '</td>';
      }

      // ----- EXTENSION POINT
      $meta_buttons = rex_extension::registerPoint('CAT_FORM_BUTTONS', '', array(
        'id' => $edit_id,
        'clang' => $clang,
      ));

      $add_buttons = '
      <input type="hidden" name="rex-api-call" value="category_edit" />
      <input type="hidden" name="category-id" value="' . $edit_id . '" />
      <input type="submit" class="rex-form-submit" name="category-edit-button" value="' . rex_i18n::msg('save_category') . '"' . rex::getAccesskey(rex_i18n::msg('save_category'), 'save') . ' />';

      $class = 'rex-table-row-active';
      if ($meta_buttons != '')
        $class .= ' rex-has-metainfo';

      $echo .= '
        <tr class="' . $class . '">
          ' . $kat_icon_td . '
          ' . $add_td . '
          <td class="rex-name"><input type="text" class="rex-form-text" id="rex-form-field-name" name="category-name" value="' . htmlspecialchars($KAT->getValue('catname')) . '" />' . $meta_buttons . '</td>
          <td class="rex-prior"><input type="text" class="rex-form-text" id="rex-form-field-prior" name="category-position" value="' . htmlspecialchars($KAT->getValue('catprior')) . '" /></td>
          <td colspan="3">' . $add_buttons . '</td>
        </tr>';

      // ----- EXTENSION POINT
      $echo .= rex_extension::registerPoint('CAT_FORM_EDIT', '', array(
        'id' => $edit_id,
        'clang' => $clang,
        'category' => $KAT,
        'catname' => $KAT->getValue('catname'),
        'catprior' => $KAT->getValue('catprior'),
        'data_colspan' => ($data_colspan + 1),
      ));

    } else {
      // --------------------- KATEGORIE WITH WRITE

      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]')) {
        $add_td = '<td class="rex-small">' . $i_category_id . '</td>';
      }

      $category_delete = '<a href="' . $context->getUrl(array('category-id' => $i_category_id, 'rex-api-call' => 'category_delete', 'catstart' => $catstart)) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . rex_i18n::msg('delete') . '</a>';

      $echo .= '
        <tr>
          ' . $kat_icon_td . '
          ' . $add_td . '
          <td class="rex-name"><a href="' . $kat_link . '">' . htmlspecialchars($KAT->getValue('catname')) . '</a></td>
          <td class="rex-prior">' . htmlspecialchars($KAT->getValue('catprior')) . '</td>
          <td class="rex-edit"><a href="' . $context->getUrl(array('edit_id' => $i_category_id, 'function' => 'edit_cat', 'catstart' => $catstart)) . '">' . rex_i18n::msg('change') . '</a></td>
          <td class="rex-delete">' . $category_delete . '</td>
          <td class="rex-status">' . $kat_status . '</td>
        </tr>';
    }

  } elseif (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($i_category_id)) {
      // --------------------- KATEGORIE WITH READ
      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]')) {
        $add_td = '<td class="rex-small">' . $i_category_id . '</td>';
      }

      $echo .= '
        <tr>
          ' . $kat_icon_td . '
          ' . $add_td . '
          <td class="rex-name"><a href="' . $kat_link . '">' . $KAT->getValue('catname') . '</a></td>
          <td class="rex-prior">' . htmlspecialchars($KAT->getValue('catprior')) . '</td>
          <td class="rex-edit"><span class="rex-strike">' . rex_i18n::msg('change') . '</span></td>
          <td class="rex-delete"><span class="rex-strike">' . rex_i18n::msg('delete') . '</span></td>
          <td class="rex-status"><span class="rex-strike ' . $status_class . '">' . $kat_status . '</span></td>
        </tr>';
  }

  $KAT->next();
}

$echo .= '
      </tbody>
    </table>';

if ($function == 'add_cat' || $function == 'edit_cat') {
  $echo .= '
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


echo rex_view::contentBlock($echo, '', 'plain');



// --------------------------------------------- ARTIKEL LISTE

$echo = '';

// --------------------- READ TEMPLATES

if ($category_id > 0 || ($category_id == 0 && !rex::getUser()->getComplexPerm('structure')->hasMountpoints())) {

  $template_select = new rex_select;
  $template_select->setName('template_id');
  $template_select->setId('rex-form-template');
  $template_select->setSize(1);

  $templates = rex_category::getTemplates($category_id);
  if (count($templates) > 0) {
    foreach ($templates as $t_id => $t_name) {
      $template_select->addOption(rex_i18n::translate($t_name, null, false), $t_id);
      $TEMPLATE_NAME[$t_id] = rex_i18n::translate($t_name);
    }
  } else {
    $template_select->addOption(rex_i18n::msg('option_no_template'), '0');
    $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');
  }

  // --------------------- ARTIKEL LIST
  $art_add_link = '';
  if ($KATPERM)
    $art_add_link = '<a class="rex-ic-article rex-ic-add" href="' . $context->getUrl(array('function' => 'add_art')) . '"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2') . '>' . rex_i18n::msg('article_add') . '</a>';

  $add_head = '';
  $add_col  = '';
  if (rex::getUser()->hasPerm('advancedMode[]')) {
    $add_head = '<th class="rex-small">' . rex_i18n::msg('header_id') . '</th>';
    $add_col  = '<col width="40" />';
  }

  // ---------- COUNT DATA
  $sql = rex_sql::factory();
  // $sql->debugsql = true;
  $sql->setQuery('SELECT COUNT(*) as artCount
        FROM
          ' . rex::getTablePrefix() . 'article
        WHERE
          ((re_id=' . $category_id . ' AND startpage=0) OR (id=' . $category_id . ' AND startpage=1))
          AND clang=' . $clang . '
        ORDER BY
          prior, name');

  // --------------------- ADD PAGINATION

  // FIXME add a realsitic rowsPerPage value
  $artPager = new rex_pager($sql->getValue('artCount'), 30, 'artstart');
  $artFragment = new rex_fragment();
  $artFragment->setVar('urlprovider', $context);
  $artFragment->setVar('pager', $artPager);
  echo $artFragment->parse('pagination.tpl');

  // ---------- READ DATA
  $sql->setQuery('SELECT *
        FROM
          ' . rex::getTablePrefix() . 'article
        WHERE
          ((re_id=' . $category_id . ' AND startpage=0) OR (id=' . $category_id . ' AND startpage=1))
          AND clang=' . $clang . '
        ORDER BY
          prior, name
        LIMIT ' . $artPager->getCursor() . ',' . $artPager->getRowsPerPage());


  // ---------- INLINE THE EDIT/ADD FORM
  if ($function == 'add_art' || $function == 'edit_art') {

    $legend = rex_i18n::msg('article_add');
    if ($function == 'edit_art')
      $legend = rex_i18n::msg('article_edit');

    $echo .= '
    <div class="rex-form" id="rex-form-structure-article">
    <form action="index.php" method="post">
      <fieldset>
        <legend><span>' . htmlspecialchars($legend) . '</span></legend>';

    $params = array();
    $params['artstart'] = $artstart;
    $echo .= $context->getHiddenInputFields($params);
  }

  // ----------- PRINT OUT THE ARTICLES

  $echo .= '
      <table id="rex-table-articles" class="rex-table" summary="' . htmlspecialchars(rex_i18n::msg('structure_articles_summary', $cat_name)) . '">
        <caption>' . htmlspecialchars(rex_i18n::msg('structure_articles_caption', $cat_name)) . '</caption>
        <thead>
          <tr>
            <th class="rex-icon">' . $art_add_link . '</th>
            ' . $add_head . '
            <th class="rex-name">' . rex_i18n::msg('header_article_name') . '</th>
            <th class="rex-prior">' . rex_i18n::msg('header_priority') . '</th>
            <th class="rex-template">' . rex_i18n::msg('header_template') . '</th>
            <th class="rex-date">' . rex_i18n::msg('header_date') . '</th>
            <th class="rex-function" colspan="3">' . rex_i18n::msg('header_status') . '</th>
          </tr>
        </thead>
        ';

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if ($sql->getRows() > 0 || $function == 'add_art') {
    $echo .= '<tbody>
          ';
  }

  // --------------------- ARTIKEL ADD FORM
  if ($function == 'add_art' && $KATPERM) {
    $defaultTemplateId = rex::getProperty('default_template_id');
    if ($defaultTemplateId > 0 && isset($TEMPLATE_NAME[$defaultTemplateId])) {
      $template_select->setSelected($defaultTemplateId);

    } else {
      // template_id vom Startartikel erben
      $sql2 = rex_sql::factory();
      $sql2->setQuery('SELECT template_id FROM ' . rex::getTablePrefix() . 'article WHERE id=' . $category_id . ' AND clang=' . $clang . ' AND startpage=1');
      if ($sql2->getRows() == 1)
        $template_select->setSelected($sql2->getValue('template_id'));
    }

    $add_td = '
      <input type="hidden" name="rex-api-call" value="article_add" />
    ';

    if (rex::getUser()->hasPerm('advancedMode[]'))
      $add_td .= '<td class="rex-small">-</td>';

      $echo .= '<tr class="rex-table-row-active">
                  <td class="rex-icon"><span class="rex-ic-article">' . rex_i18n::msg('article_add') . '</span></td>
                  ' . $add_td . '
                  <td class="rex-name"><input type="text" id="rex-form-field-name" name="article-name" /></td>
                  <td class="rex-prior"><input type="text" id="rex-form-field-prior" name="article-position" value="' . ($sql->getRows() + 1) . '" /></td>
                  <td class="rex-template">' . $template_select->get() . '</td>
                  <td class="rex-date">' . rex_formatter :: format(time(), 'strftime', 'date') . '</td>
                  <td colspan="3"><input type="submit" name="artadd_function" value="' . rex_i18n::msg('article_add') . '"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'save') . ' /></td>
                </tr>
                ';
  }

  // --------------------- ARTIKEL LIST

  for ($i = 0; $i < $sql->getRows(); $i++) {

    if ($sql->getValue('startpage') == 1)
      $class = 'rex-ic-article-startpage';
    else
      $class = 'rex-ic-article';

    // --------------------- ARTIKEL EDIT FORM

    if ($function == 'edit_art' && $sql->getValue('id') == $article_id && $KATPERM) {
      $add_td = '
        <input type="hidden" name="rex-api-call" value="article_edit" />
      ';

      if (rex::getUser()->hasPerm('advancedMode[]'))
        $add_td .= '<td class="rex-small">' . $sql->getValue('id') . '</td>';

      $template_select->setSelected($sql->getValue('template_id'));

      $echo .= '<tr class="rex-table-row-active">
                  <td class="rex-icon"><a class="' . $class . '" href="' . $context->getUrl(array('page' => 'content', 'article_id' => $sql->getValue('id'))) . '">' . htmlspecialchars($sql->getValue('name')) . '</a></td>
                  ' . $add_td . '
                  <td class="rex-name"><input type="text" id="rex-form-field-name" name="article-name" value="' . htmlspecialchars($sql->getValue('name')) . '" /></td>
                  <td class="rex-prior"><input type="text" id="rex-form-field-prior" name="article-position" value="' . htmlspecialchars($sql->getValue('prior')) . '" /></td>
                  <td class="rex-template">' . $template_select->get() . '</td>
                  <td class="rex-date">' . rex_formatter :: format($sql->getValue('createdate'), 'strftime', 'date') . '</td>
                  <td colspan="3"><input type="submit" name="artedit_function" value="' . rex_i18n::msg('article_save') . '"' . rex::getAccesskey(rex_i18n::msg('article_save'), 'save') . ' /></td>
                </tr>
                ';

    } elseif ($KATPERM) {
      // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER

      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
        $add_td = '<td class="rex-small">' . $sql->getValue('id') . '</td>';

      $article_status = $artStatusTypes[$sql->getValue('status')][0];
      $article_class = $artStatusTypes[$sql->getValue('status')][1];

      $add_extra = '';
      if ($sql->getValue('startpage') == 1) {
        $add_extra = '<td class="rex-delete"><span class="rex-strike">' . rex_i18n::msg('delete') . '</span></td>
                      <td class="rex-status"><span class="rex-strike ' . $article_class . '">' . $article_status . '</span></td>';
      } else {
        if ($KATPERM && rex::getUser()->hasPerm('publishArticle[]'))
          $article_status = '<a href="' . $context->getUrl(array('article_id' => $sql->getValue('id'), 'rex-api-call' => 'article_status', 'artstart' => $artstart)) . '" class="' . $article_class . '">' . $article_status . '</a>';
        else
          $article_status = '<span class="rex-strike ' . $article_class . '">' . $article_status . '</span>';

        $article_delete = '<a href="' . $context->getUrl(array('article_id' => $sql->getValue('id'), 'rex-api-call' => 'article_delete', 'artstart' => $artstart)) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . rex_i18n::msg('delete') . '</a>';

        $add_extra = '<td class="rex-delete">' . $article_delete . '</td>
                      <td class="rex-status">' . $article_status . '</td>';
      }

      $editModeUrl = $context->getUrl(array('page' => 'content', 'article_id' => $sql->getValue('id'), 'mode' => 'edit'));
      $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';

      $echo .= '<tr>
                  <td class="rex-icon"><a class="' . $class . '" href="' . $editModeUrl . '">' . htmlspecialchars($sql->getValue('name')) . '</a></td>
                  ' . $add_td . '
                  <td class="rex-name"><a href="' . $editModeUrl . '">' . htmlspecialchars($sql->getValue('name')) . '</a></td>
                  <td class="rex-prior">' . htmlspecialchars($sql->getValue('prior')) . '</td>
                  <td class="rex-template">' . $tmpl . '</td>
                  <td class="rex-date">' . rex_formatter :: format($sql->getValue('createdate'), 'strftime', 'date') . '</td>
                  <td><a href="' . $context->getUrl(array('article_id' => $sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $artstart)) . '">' . rex_i18n::msg('change') . '</a></td>
                  ' . $add_extra . '
                </tr>
                ';

    } else {
      // --------------------- ARTIKEL NORMAL VIEW | NO EDIT NO ENTER

      $add_td = '';
      if (rex::getUser()->hasPerm('advancedMode[]'))
        $add_td = '<td class="rex-small">' . $sql->getValue('id') . '</td>';

      $art_status = $artStatusTypes[$sql->getValue('status')][0];
      $art_status_class = $artStatusTypes[$sql->getValue('status')][1];
      $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';

      $echo .= '<tr>
                  <td class="rex-icon"><span class="' . $class . '">' . htmlspecialchars($sql->getValue('name')) . '"</span></td>
                  ' . $add_td . '
                  <td class="rex-name">' . htmlspecialchars($sql->getValue('name')) . '</td>
                  <td class="rex-prior">' . htmlspecialchars($sql->getValue('prior')) . '</td>
                  <td class="rex-template">' . $tmpl . '</td>
                  <td class="rex-date">' . rex_formatter :: format($sql->getValue('createdate'), 'strftime', 'date') . '</td>
                  <td class="rex-edit"><span class="rex-strike">' . rex_i18n::msg('change') . '</span></td>
                  <td class="rex-delete"><span class="rex-strike">' . rex_i18n::msg('delete') . '</span></td>
                  <td class="rex-status"><span class="rex-strike ' . $art_status_class . '">' . $art_status . '</span></td>
                </tr>
                ';
    }

    $sql->next();
  }

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if ($sql->getRows() > 0 || $function == 'add_art') {
    $echo .= '
        </tbody>';
  }

  $echo .= '
      </table>';

  if ($function == 'add_art' || $function == 'edit_art') {
    $echo .= '
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


echo rex_view::contentBlock($echo, '', 'plain');
