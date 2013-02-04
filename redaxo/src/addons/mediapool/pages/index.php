<?php

/**
 *
 * @package redaxo5
 */

// TODOS
// - wysiwyg image pfade anschauen und kontrollieren
// - import checken
// - mehrere ebenen in kategorienedit  einbauen

global $subpage, $ftitle, $warning, $info;

// -------------- Defaults
$subpage      = rex_be_controller::getCurrentPagePart(2);
$func         = rex_request('func', 'string');
$info         = rex_request('info', 'string');
$warning      = rex_request('warning', 'string');
$args         = rex_request('args', 'array');


// -------------- Additional Args
$arg_url = array('args' => $args);
$arg_fields = '';
foreach ($args as $arg_name => $arg_value) {
  $arg_fields .= '<input type="hidden" name="args[' . $arg_name . ']" value="' . htmlspecialchars($arg_value) . '" />' . "\n";
}

// ----- opener_input_field setzen
$opener_link = rex_request('opener_link', 'string');
$opener_input_field = rex_request('opener_input_field', 'string', '');

if ($opener_input_field != '') {
  $arg_url['opener_input_field'] = $opener_input_field;
  $arg_fields .= '<input type="hidden" name="opener_input_field" value="' . htmlspecialchars($opener_input_field) . '" />' . "\n";
}

// -------------- CatId in Session speichern
$file_id = rex_request('file_id', 'int');
$file_name = rex_request('file_name', 'string');
$rex_file_category = rex_request('rex_file_category', 'int', -1);

if ($file_name != '') {
  $sql = rex_sql::factory();
  $sql->setQuery('select * from ' . rex::getTablePrefix() . "media where filename='$file_name'");
  if ($sql->getRows() == 1) {
    $file_id = $sql->getValue('file_id');
    $rex_file_category = $sql->getValue('category_id');
  }
}

if ($rex_file_category == -1) {
  $rex_file_category = rex_session('media[rex_file_category]', 'int');
}


$gc = rex_sql::factory();
$gc->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE id=' . $rex_file_category);
if ($gc->getRows() != 1) {
  $rex_file_category = 0;
  $rex_file_category_name = rex_i18n::msg('pool_kats_no');
} else {
  $rex_file_category_name = $gc->getValue('name');
}

rex_set_session('media[rex_file_category]', $rex_file_category);

// -------------- PERMS
$PERMALL = rex::getUser()->getComplexPerm('media')->hasCategoryPerm(0);

// -------------- Header
$subline = rex_be_controller::getPageObject('mediapool')->getPage()->getSubPages();

foreach ($subline as $sp) {
  $sp->setHref(rex_url::backendPage($sp->getFullKey(), $arg_url));
}

// ----- EXTENSION POINT
$subline = rex_extension::registerPoint('PAGE_MEDIAPOOL_MENU', $subline,
  array(
    'subpage' => $subpage,
  )
);

echo rex_view::title(rex_i18n::msg('pool_media'), $subline);

// -------------- Messages
if ($info != '') {
  echo rex_view::info($info);
  $info = '';
}
if ($warning != '') {
  echo rex_view::warning($warning);
  $warning = '';
}

// -------------- Javascripts
?>
<script type="text/javascript">
<!--

function selectMedia(filename, alt)
{
  <?php
  if ($opener_input_field != '') {
    echo 'opener.document.getElementById("' . $opener_input_field . '").value = filename;';
  }
  ?>
  self.close();
}

function selectMedialist(filename)
{
  <?php
    if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
      $id = substr($opener_input_field, 14, strlen($opener_input_field));
      echo 'var medialist = "REX_MEDIALIST_SELECT_' . $id . '";

            var source = opener.document.getElementById(medialist);
            var sourcelength = source.options.length;

            option = opener.document.createElement("OPTION");
            option.text = filename;
            option.value = filename;

            source.options.add(option, sourcelength);
            opener.writeREXMedialist(' . $id . ');';

    }
  ?>
}

function selectMediaListArray(files)
{
  <?php
    if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
      $id = substr($opener_input_field, 14, strlen($opener_input_field));
      echo 'var medialist = "REX_MEDIALIST_SELECT_' . $id . '";

            var source = opener.document.getElementById(medialist);
            var sourcelength = source.options.length;

            var files = getObjArray(files);

            for(var i = 0; i < files.length; i++)
            {
              if (files[i].checked)
              {
                option = opener.document.createElement("OPTION");
                option.text = files[i].value;
                option.value = files[i].value;

                source.options.add(option, sourcelength);
                sourcelength++;
              }
            }

            opener.writeREXMedialist(' . $id . ');';

    }
  ?>
}

function insertImage(src,alt)
{
  window.opener.insertImage("<?php echo rex_url::media(); ?>" + src, alt);
  self.close();
}

function insertLink(src)
{
  window.opener.insertFileLink("<?php echo rex_url::media(); ?>" + src);
  self.close();
}

function openPage(src)
{
  window.opener.location.href = src;
  self.close();
}

//-->
</script>
<?php

// -------------- Include Page
switch ($subpage) {
  case 'upload'    : $file = 'upload.php'; break;
  case 'structure' : $file = 'structure.php'; break;
  case 'sync'      : $file = 'sync.php'; break;
  default          : $file = 'media.php'; break;
}

require __DIR__ . '/' . $file;
