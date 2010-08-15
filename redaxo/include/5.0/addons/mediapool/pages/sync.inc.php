<?php

// *************************************** SYNC FUNCTIONS


// ----- SYNC DB WITH FILES DIR
if($PERMALL)
{
  // ---- Dateien aus dem Ordner lesen
  $folder_files = array();
  $handle = opendir($REX['MEDIAFOLDER']);
  if($handle) {
    while(($file = readdir($handle)) !== false)
    {
      if(!is_file($REX['MEDIAFOLDER'] .'/'. $file)) continue;

      // Tempfiles nicht synchronisieren
      if(substr($file, 0, strlen($REX['TEMP_PREFIX'])) != $REX['TEMP_PREFIX'])
      {
        $folder_files[] = $file;
      }
    }
    closedir($handle);
  }

  // ---- Dateien aus der DB lesen
  $db = rex_sql::factory();
  $db->setQuery('SELECT filename FROM '. $REX['TABLE_PREFIX'].'file');
  $db_files = array();

  for($i=0;$i<$db->getRows();$i++)
  {
    $db_files[] = $db->getValue('filename');
    $db->next();
  }

  $diff_files = array_diff($folder_files, $db_files);
  $diff_count = count($diff_files);

  if(rex_post('save', 'boolean') && rex_post('sync_files', 'boolean'))
  {
    $sync_files = rex_post('sync_files', 'array');
    $ftitle     = rex_post('ftitle', 'string');
    
    if($diff_count > 0)
    {
      foreach($sync_files as $file)
      {
        // hier mit is_int, wg kompatibilität zu PHP < 4.2.0
        if(!is_int($key = array_search($file, $diff_files))) continue;

        if(rex_mediapool_syncFile($file,$rex_file_category,$ftitle,'',''))
        {
          unset($diff_files[$key]);
          $info = $I18N->msg('pool_sync_files_synced');
        }else
        {

        }
      }
      // diff count neu berechnen, da (hoffentlich) diff files in die db geladen wurden
      $diff_count = count($diff_files);
    }else
    {
 
    }
  }elseif(rex_post('save', 'boolean'))
  {
  	$warning = $I18N->msg('pool_file_not_found');
  }

  echo rex_mediapool_Syncform($rex_file_category);

  $title = $I18N->msg('pool_sync_affected_files');
  if(!empty($diff_count))
  {
    $title .= ' ('. $diff_count .')';
  }
  echo '<fieldset class="rex-form-col-1">
          <legend>'. $title .'</legend>
          <div class="rex-form-wrapper">';

  if($diff_count > 0)
  {
    foreach($diff_files as $file)
    {
      echo '<div class="rex-form-row">
              <p class="rex-form-checkbox rex-form-label-right">';
      if(is_writable($REX['MEDIAFOLDER'] .'/'. $file))
      {
        echo '<input class="rex-form-checkbox" type="checkbox" id="sync_file_'. $file .'" name="sync_files[]" value="'. $file .'" />
              <label for="sync_file_'. $file .'">'. $file .'</label>';
      }
      else
      {
        echo $file .' - '.  $I18N->msg('pool_file_not_writable') . "\n";
      }
      echo '  </p>
            </div>';
    }

    echo '<div class="rex-form-row">
            <p class="rex-form-checkbox rex-form-label-right">
              <input class="rex-form-checkbox" type="checkbox" name="checkie" id="checkie" value="0" onchange="setAllCheckBoxes(\'sync_files[]\',this)" />
              <label for="checkie">'. $I18N->msg('pool_select_all') .'</label>
            </p>
          </div>';

  }
  else
  {
    echo '<div class="rex-form-row">
            <p class="rex-form-notice">
              <span class="rex-form-notice"><strong>'. $I18N->msg('pool_sync_no_diffs') .'</strong></span>
            </p>
          </div>';
  }

  echo '</div>
        </fieldset>
      </form>
    </div>';
}