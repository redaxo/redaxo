<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$info = '';
$warning = '';

// ------------------------------ Requestvars
$function       = rex_request('function', 'string');
$impname        = rex_request('impname', 'string');
$exportfilename = rex_post('exportfilename', 'string');
$exporttype     = rex_post('exporttype', 'string');
$exportdl       = rex_post('exportdl', 'boolean');
$EXPDIR         = rex_post('EXPDIR', 'array');

@set_time_limit(0);

if ($impname != '')
{
  $impname = str_replace("/", "", $impname);

  if ($function == "dbimport" && substr($impname, -4, 4) != ".sql")
    $impname = "";
  elseif ($function == "fileimport" && substr($impname, -7, 7) != ".tar.gz")
    $impname = "";
}

if ($exportfilename == '')
  $exportfilename = 'rex_'.$REX['VERSION'].'_'.date("Ymd");

if ($function == "delete")
{
  // ------------------------------ FUNC DELETE
  if (unlink(getImportDir().'/'.$impname));
  $info = $I18N->msg("im_export_file_deleted");
}
elseif ($function == "dbimport")
{
  // ------------------------------ FUNC DBIMPORT

  // noch checken das nicht alle tabellen geloescht werden
  // install/temp.sql aendern
  if (isset ($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && $impname == "")
  {
    $warning = $I18N->msg("im_export_no_import_file_chosen_or_wrong_version")."<br>";
  }
  else
  {
    if ($impname != "")
    {
      $file_temp = getImportDir().'/'.$impname;
    }
    else
    {
      $file_temp = getImportDir().'/temp.sql';
    }

    if ($impname != "" || @ move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp))
    {
      $state = rex_a1_import_db($file_temp);
      $info = $state['message'];

      // temp datei löschen
      if ($impname == "")
      {
        @ unlink($file_temp);
      }
    }
    else
    {
      $warning = $I18N->msg("im_export_file_could_not_be_uploaded")." ".$I18N->msg("im_export_you_have_no_write_permission_in", "addons/import_export/files/")." <br>";
    }
  }

}
elseif ($function == "fileimport")
{
  // ------------------------------ FUNC FILEIMPORT

  if (isset($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && $impname == "")
  {
    $warning = $I18N->msg("im_export_no_import_file_chosen")."<br/>";
  }
  else
  {
    if ($impname == "")
    {
      $file_temp = getImportDir().'/temp.tar.gz';
    }
    else
    {
      $file_temp = getImportDir().'/'.$impname;
    }
    
    if ($impname != "" || @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp))
    {
      $return = rex_a1_import_files($file_temp);
			if($return['state'])
			{
      	$info = $return['message'];
			}
			else
			{
				$warning = $return['message'];
			}

      // temp datei löschen
      if ($impname == "")
      {
        @ unlink($file_temp);
      }
    }
    else
    {
      $warning = $I18N->msg("im_export_file_could_not_be_uploaded")." ".$I18N->msg("im_export_you_have_no_write_permission_in", "addons/import_export/files/")." <br>";
    }
  }

}
if ($info != '')
{
  echo rex_info($info);
}
if ($warning != '')
{
  echo rex_warning($warning);
}

?>

<div class="rex-area">
    <h3 class="rex-hl2"><?php echo $I18N->msg('im_export_import'); ?></h3>
    
    <div class="rex-area-content">
      <p class="rex-tx1"><?php echo $I18N->msg('im_export_intro_import') ?></p>
      
      <div class="rex-form" id="rex-form-import-data">
        <form action="index.php" enctype="multipart/form-data" method="post" onsubmit="return confirm('<?php echo $I18N->msg('im_export_proceed_db_import') ?>')">

          <fieldset class="rex-form-col-1">
          
            <legend><?php echo $I18N->msg('im_export_database'); ?></legend>
						           
            <div class="rex-form-wrapper">
              <input type="hidden" name="page" value="import_export" />
  						<input type="hidden" name="subpage" value="import" /> 
              <input type="hidden" name="function" value="dbimport" />
              
              <div class="rex-form-row">
                <p class="rex-form-file">
                  <label for="importdbfile"><?php echo $I18N->msg('im_export_database'); ?></label>
                  <input class="rex-form-file" type="file" id="importdbfile" name="FORM[importfile]" size="18" />
                </p>
              </div>
              <div class="rex-form-row">
                <p class="rex-form-submit">
                  <input type="submit" class="rex-form-submit" value="<?php echo $I18N->msg('im_export_db_import') ?>" />
                </p>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
      
      <table class="rex-table" summary="<?php echo $I18N->msg('im_export_export_db_summary'); ?>">
        <caption><?php echo $I18N->msg('im_export_export_db_caption'); ?></caption>
        <colgroup>
          <col width="*" />
          <col width="15%" span="3"/>
        </colgroup>
        <thead>
          <tr>
            <th><?php echo $I18N->msg('im_export_filename'); ?></th>
            <th><?php echo $I18N->msg('im_export_filesize'); ?></th>
            <th><?php echo $I18N->msg('im_export_createdate'); ?></th>
            <th colspan="2"><?php echo $I18N->msg('im_export_function'); ?></th>
          </tr>
        </thead>
        <tbody>
<?php
  $dir = getImportDir();
  $folder = readImportFolder('.sql');

  foreach ($folder as $file)
  {
    $filepath = $dir.'/'.$file;
    $filec = date('d.m.Y H:i', filemtime($filepath));
    $filesize = OOMedia::_getFormattedSize(filesize($filepath));

    echo '<tr>
            <td>'. $file .'</td>
            <td>'.$filesize.'</td>
            <td>'. $filec .'</td>
            <td><a href="index.php?page=import_export&amp;subpage=import&amp;function=dbimport&amp;impname='. $file .'" title="'. $I18N->msg('im_export_import_file') .'" onclick="return confirm(\''. $I18N->msg('im_export_proceed_db_import') .'\')">'. $I18N->msg('im_export_import') .'</a></td>
            <td><a href="index.php?page=import_export&amp;subpage=import&amp;function=delete&amp;impname='. $file .'" title="'. $I18N->msg('im_export_delete_file') .'" onclick="return confirm(\''. $I18N->msg('im_export_delete') .' ?\')">'. $I18N->msg('im_export_delete') .'</a></td>
          </tr>
  ';
  }
?>
        </tbody>
      </table>

      <!-- FILE IMPORT -->
      <div class="rex-form" id="rex-form-import-files">
        <form action="index.php" enctype="multipart/form-data" method="post" onsubmit="return confirm('<?php echo $I18N->msg('im_export_proceed_file_import') ?>')" >
          <fieldset class="rex-form-col-1">
            <legend><?php echo $I18N->msg('im_export_files'); ?></legend>
            
            <div class="rex-form-wrapper">
              <input type="hidden" name="page" value="import_export" />
              <input type="hidden" name="subpage" value="import" />
              <input type="hidden" name="function" value="fileimport" />
              
              <div class="rex-form-row">
                <p class="rex-form-file">
                  <label for="importtarfile"><?php echo $I18N->msg('im_export_files'); ?></label>
                  <input class="rex-form-file" type="file" id="importtarfile" name="FORM[importfile]" size="18" />
                </p>
              </div>
              <div class="rex-form-row">
                <p class="rex-form-submit">
                  <input class="rex-form-submit" type="submit" value="<?php echo $I18N->msg('im_export_db_import') ?>" />
                </p>
              </div>
            </div>
          </fieldset>
        </form>
      </div>

      <table class="rex-table" summary="<?php echo $I18N->msg('im_export_export_file_summary'); ?>">
        <caption><?php echo $I18N->msg('im_export_export_file_caption'); ?></caption>
        <colgroup>
          <col width="*" />
          <col width="15%" span="3"/>
        </colgroup>
        <thead>
          <tr>
            <th><?php echo $I18N->msg('im_export_filename'); ?></th>
            <th><?php echo $I18N->msg('im_export_filesize'); ?></th>
            <th><?php echo $I18N->msg('im_export_createdate'); ?></th>
            <th colspan="2"><?php echo $I18N->msg('im_export_function'); ?></th>
          </tr>
        </thead>
        <tbody>
<?php
  $dir = getImportDir();
  $folder = readImportFolder('.tar.gz');

  foreach ($folder as $file)
  {
    $filepath = $dir.'/'.$file;
    $filec = date('d.m.Y H:i', filemtime($filepath));
    $filesize = OOMedia::_getFormattedSize(filesize($filepath));

    echo '<tr>
            <td>'. $file .'</td>
            <td>'.$filesize.'</td>
            <td>'. $filec .'</td>
            <td><a href="index.php?page=import_export&amp;subpage=import&amp;function=fileimport&amp;impname='. $file .'" title="'. $I18N->msg('im_export_import_file') .'" onclick="return confirm(\''. $I18N->msg('im_export_proceed_file_import') .'\')">'. $I18N->msg('im_export_import') .'</a></td>
            <td><a href="index.php?page=import_export&amp;subpage=import&amp;function=delete&amp;impname='. $file .'" title="'. $I18N->msg('im_export_delete_file') .'" onclick="return confirm(\''. $I18N->msg('im_export_delete') .' ?\')">'. $I18N->msg('im_export_delete') .'</a></td>
          </tr>';
  }
?>
        </tbody>
      </table>
    </div>
  
 
  <div class="rex-clearer"></div>
</div><!-- END rex-area -->
