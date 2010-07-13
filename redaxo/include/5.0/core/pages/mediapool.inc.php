<?php
/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// TODOS
// - wysiwyg image pfade anschauen und kontrollieren
// - import checken
// - mehrere ebenen in kategorienedit  einbauen

// -------------- Defaults
$subpage      = rex_request('subpage', 'string');
$func         = rex_request('func', 'string');
$media_method = rex_request('media_method', 'string');
$info         = rex_request('info', 'string');
$warning      = rex_request('warning', 'string');


// -------------- Additional Args
$arg_url = '';
$arg_fields = '';
foreach(rex_request('args', 'array') as $arg_name => $arg_value)
{
  $arg_url .= '&amp;args['. urlencode($arg_name) .']='. urlencode($arg_value);
  $arg_fields .= '<input type="hidden" name="args['. $arg_name .']" value="'. htmlspecialchars($arg_value) .'" />'. "\n";
}

// ----- opener_input_field setzen
$opener_link = rex_request('opener_link', 'string');
$opener_input_field = rex_request('opener_input_field', 'string', '');

if($opener_input_field != "")
{
  $arg_url .= '&amp;opener_input_field='.urlencode($opener_input_field);
  $arg_fields .= '<input type="hidden" name="opener_input_field" value="'. htmlspecialchars($opener_input_field) .'" />'. "\n";
}

// -------------- CatId in Session speichern
$file_id = rex_request('file_id', 'int');
$file_name = rex_request('file_name', 'string');
$rex_file_category = rex_request('rex_file_category', 'rex-mediacategory-id', -1);

if ($file_name != "")
{
  $sql = rex_sql::factory();
  $sql->setQuery("select * from ".$REX['TABLE_PREFIX']."file where filename='$file_name'");
  if ($sql->getRows()==1)
  {
    $file_id = $sql->getValue("file_id");
    $rex_file_category = $sql->getValue("category_id");
  }
}

if($rex_file_category == -1)
{
  $rex_file_category = rex_session('media[rex_file_category]', 'int');
}


$gc = rex_sql::factory();
$gc->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'file_category WHERE id='. $rex_file_category);
if ($gc->getRows() != 1)
{
  $rex_file_category = 0;
  $rex_file_category_name = $I18N->msg('pool_kats_no');
}else
{
  $rex_file_category_name = $gc->getValue('name');
}

rex_set_session('media[rex_file_category]', $rex_file_category);

// -------------- PERMS
$PERMALL = false;
if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('media[0]')) $PERMALL = true;

// -------------- Header
$subline = array(
  array('', $I18N->msg('pool_file_list')),
  array('add_file', $I18N->msg('pool_file_insert')),
);

if($PERMALL)
{
  $subline[] = array('categories', $I18N->msg('pool_cat_list'));
  $subline[] = array('sync', $I18N->msg('pool_sync_files'));
}

// Arg Url an Menulinks anhaengen
foreach($subline as $key => $item)
{
  $subline[$key][2] = '';
  $subline[$key][3] = $arg_url;
}

// ----- EXTENSION POINT
$subline = rex_register_extension_point('PAGE_MEDIAPOOL_MENU', $subline,
  array(
    'subpage' => $subpage,
  )
);

$title = $I18N->msg('pool_media');
rex_title($title, $subline);

// -------------- Messages
if ($info != '')
{
  echo rex_info($info);
  $info = '';
}
if ($warning != '')
{
  echo rex_warning($warning);
  $warning = '';
}

// -------------- Javascripts
?>
<script type="text/javascript">
<!--

function selectMedia(filename, alt)
{
  <?php
  if ($opener_input_field != '')
  {
    echo 'opener.document.getElementById("'.$opener_input_field.'").value = filename;';
  }
  ?>
  self.close();
}

function selectMedialist(filename)
{
  <?php
    if (substr($opener_input_field,0,14) == 'REX_MEDIALIST_')
    {
      $id = substr($opener_input_field,14,strlen($opener_input_field));
      echo 'var medialist = "REX_MEDIALIST_SELECT_'. $id .'";

            var source = opener.document.getElementById(medialist);
            var sourcelength = source.options.length;

            option = opener.document.createElement("OPTION");
            option.text = filename;
            option.value = filename;

            source.options.add(option, sourcelength);
            opener.writeREXMedialist('. $id .');';

    }
  ?>
}

function selectMediaListArray(files)
{
  <?php
    if (substr($opener_input_field,0,14) == 'REX_MEDIALIST_')
    {
      $id = substr($opener_input_field,14,strlen($opener_input_field));
      echo 'var medialist = "REX_MEDIALIST_SELECT_'. $id .'";

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

            opener.writeREXMedialist('. $id .');';

    }
  ?>
}

function insertImage(src,alt)
{
  window.opener.insertImage('files/' + src, alt);
  self.close();
}

function insertLink(src)
{
  window.opener.insertFileLink('files/' + src);
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
switch($subpage)
{
  case 'add_file'  : $file = 'mediapool.upload.inc.php'; break;
  case 'categories': $file = 'mediapool.structure.inc.php'; break;
  case 'sync'      : $file = 'mediapool.sync.inc.php'; break;
  default          : $file = 'mediapool.media.inc.php'; break;
}

require $REX['INCLUDE_PATH'].'/pages/'.$file;