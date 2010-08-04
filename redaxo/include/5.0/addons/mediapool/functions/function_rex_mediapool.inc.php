<?php

/**
 * Funktionensammlung für den Medienpool
 *
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Erstellt einen Filename der eindeutig ist für den Medienpool
 * @param $FILENAME Dateiname
 */
function rex_mediapool_filename($FILENAME, $doSubindexing = true)
{
  global $REX;

  // ----- neuer filename und extension holen
  $NFILENAME = strtolower($FILENAME);
  $NFILENAME = str_replace(array('ä','ö', 'ü', 'ß'),array('ae', 'oe', 'ue', 'ss'),$NFILENAME);
  $NFILENAME = preg_replace('/[^a-zA-Z0-9.\-\+]/','_',$NFILENAME);
  if (strrpos($NFILENAME,'.') != '')
  {
    $NFILE_NAME = substr($NFILENAME,0,strlen($NFILENAME)-(strlen($NFILENAME)-strrpos($NFILENAME,'.')));
    $NFILE_EXT  = substr($NFILENAME,strrpos($NFILENAME,'.'),strlen($NFILENAME)-strrpos($NFILENAME,'.'));
  }else
  {
    $NFILE_NAME = $NFILENAME;
    $NFILE_EXT  = '';
  }

  // ---- ext checken - alle scriptendungen rausfiltern
  if (in_array($NFILE_EXT,$REX['MEDIAPOOL']['BLOCKED_EXTENSIONS']))
  {
    $NFILE_NAME .= $NFILE_EXT;
    $NFILE_EXT = '.txt';
  }

  $NFILENAME = $NFILE_NAME.$NFILE_EXT;

  if($doSubindexing)
  {
    // ----- datei schon vorhanden -> namen aendern -> _1 ..
    if (file_exists($REX['MEDIAFOLDER'].'/'.$NFILENAME))
    {
      $cnt = 1;
      while(file_exists($REX['MEDIAFOLDER'].'/'.$NFILE_NAME.'_'.$cnt.$NFILE_EXT))
        $cnt++;

      $NFILENAME = $NFILE_NAME.'_'.$cnt.$NFILE_EXT;
    }
  }

  return $NFILENAME;
}

/**
 * Holt ein upgeloadetes File und legt es in den Medienpool
 * Dabei wird kontrolliert ob das File schon vorhanden ist und es
 * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben
 *
 * @param $FILE
 * @param $rex_file_category
 * @param $FILEINFOS
 * @param $userlogin
*/
function rex_mediapool_saveMedia($FILE, $rex_file_category, $FILEINFOS, $userlogin = null, $doSubindexing = TRUE){

  global $REX,$I18N;

  $rex_file_category = (int) $rex_file_category;

  $gc = rex_sql::factory();
  $gc->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'file_category WHERE id='. $rex_file_category);
	if ($gc->getRows() != 1)
	{
  	$rex_file_category = 0;
	}

  $isFileUpload = isset($FILE['tmp_name']);
  if ($isFileUpload) $doSubindexing = TRUE;

  $FILENAME = $FILE['name'];
  $FILESIZE = $FILE['size'];
  $FILETYPE = $FILE['type'];
  $NFILENAME = rex_mediapool_filename($FILENAME, $doSubindexing);
  $message = '';

  // ----- alter/neuer filename
  $srcFile = $REX['MEDIAFOLDER'].'/'.$FILENAME;
  $dstFile = $REX['MEDIAFOLDER'].'/'.$NFILENAME;

  $success = true;
  if($isFileUpload) // Fileupload?
  {
    if(!@move_uploaded_file($FILE['tmp_name'],$dstFile))
    {
      $message .= $I18N->msg("pool_file_movefailed");
      $success = false;
    }
  }
  else // Filesync?
  {
    if(!@rename($srcFile,$dstFile))
    {
      $message .= $I18N->msg("pool_file_movefailed");
      $success = false;
    }
  }

  if($success)
  {
    @chmod($dstFile, $REX['FILEPERM']);

    // get widht height
    $size = @getimagesize($dstFile);

    if($FILETYPE == '' && isset($size['mime']))
      $FILETYPE = $size['mime'];
      
    $FILESQL = rex_sql::factory();
    $FILESQL->setTable($REX['TABLE_PREFIX'].'file');
    $FILESQL->setValue('filetype',$FILETYPE);
    $FILESQL->setValue('title',$FILEINFOS['title']);
    $FILESQL->setValue('filename',$NFILENAME);
    $FILESQL->setValue('originalname',$FILENAME);
    $FILESQL->setValue('filesize',$FILESIZE);

    if($size)
    {
      $FILESQL->setValue('width',$size[0]);
      $FILESQL->setValue('height',$size[1]);
    }

    $FILESQL->setValue('category_id',$rex_file_category);
    $FILESQL->addGlobalCreateFields($userlogin);
    $FILESQL->addGlobalUpdateFields($userlogin);
    $FILESQL->insert();

    $message .= $I18N->msg("pool_file_added");
    
    rex_deleteCacheMediaList($rex_file_category);
  }

  $RETURN['title'] = $FILEINFOS['title'];
  $RETURN['type'] = $FILETYPE;
  $RETURN['msg'] = $message;
  // Aus BC gruenden hier mit int 1/0
  $RETURN['ok'] = $success ? 1 : 0;
  $RETURN['filename'] = $NFILENAME;
  $RETURN['old_filename'] = $FILENAME;

  if($size)
  {
    $RETURN['width'] = $size[0];
    $RETURN['height'] = $size[1];
  }

  // ----- EXTENSION POINT
  if ($success)
    rex_register_extension_point('MEDIA_ADDED','',$RETURN);

  return $RETURN;
}


/**
 * Holt ein upgeloadetes File und legt es in den Medienpool
 * Dabei wird kontrolliert ob das File schon vorhanden ist und es
 * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben
 *
 * @param $FILE
 * @param $rex_file_category
 * @param $FILEINFOS
 * @param $userlogin
*/
function rex_mediapool_updateMedia($FILE, &$FILEINFOS, $userlogin = null){

  global $REX,$I18N;

	$RETURN = array();
  
  $FILESQL = rex_sql::factory();
  // $FILESQL->debugsql = 1;
  $FILESQL->setTable($REX['TABLE_PREFIX'].'file');
  $FILESQL->setWhere('file_id='. $FILEINFOS["file_id"]);
  $FILESQL->setValue('title',$FILEINFOS["title"]);
  $FILESQL->setValue('category_id',$FILEINFOS["rex_file_category"]);

  $msg = '';

  $updated = false;
  if ($_FILES['file_new']['name'] != '' && $_FILES['file_new']['name'] != 'none')
  {
    $ffilename = $_FILES['file_new']['tmp_name'];
    $ffiletype = $_FILES['file_new']['type'];
    $ffilesize = $_FILES['file_new']['size'];

    if ($ffiletype == $FILEINFOS["filetype"] || OOMedia::compareImageTypes($ffiletype,$FILEINFOS["filetype"]))
    {
      if (move_uploaded_file($ffilename,$REX['MEDIAFOLDER'] .'/'. $FILEINFOS["filename"]) ||
          copy($ffilename,$REX['MEDIAFOLDER'] .'/'. $FILEINFOS["filename"]))
      {
        $RETURN["msg"] = $I18N->msg('pool_file_changed');
				$FILEINFOS["filetype"] = $ffiletype;
				$FILEINFOS["filesize"] = $ffilesize;

        $FILESQL->setValue('filetype',$FILEINFOS["filetype"]);
        // $FILESQL->setValue('originalname',$ffilename);
        $FILESQL->setValue('filesize',$FILEINFOS["filesize"]);
        if($size = @getimagesize($REX['MEDIAFOLDER'] .'/'. $FILEINFOS["filename"]))
        {
          $FILESQL->setValue('width',$size[0]);
          $FILESQL->setValue('height',$size[1]);
        }
        @chmod($REX['MEDIAFOLDER'].'/'. $FILEINFOS["filename"], $REX['FILEPERM']);
        $updated = true;
      }else
      {
          $RETURN["msg"] = $I18N->msg('pool_file_upload_error');
      }
    }else
    {
      $RETURN["msg"] = $I18N->msg('pool_file_upload_errortype');
    }
  }

  // Aus BC gruenden hier mit int 1/0
  $RETURN["ok"] = $updated ? 1 : 0;
  if(!isset($RETURN["msg"]))
  {
    $RETURN["msg"] = $I18N->msg('pool_file_infos_updated');
    $RETURN["ok"] = 1;
  }
  if($RETURN['ok'] == 1)
  {
    $RETURN["filename"] = $FILEINFOS["filename"];
    $RETURN["filetype"] = $FILEINFOS["filetype"];
    $RETURN["file_id"] = $FILEINFOS["file_id"];
  }
  
	$FILESQL->addGlobalUpdateFields();
	$FILESQL->update();
  
  rex_deleteCacheMedia($FILEINFOS["filename"]);


/*
$RETURN['title'] = $FILEINFOS['title'];
$RETURN['type'] = $FILETYPE;
$RETURN['msg'] = $message;
// Aus BC gruenden hier mit int 1/0
$RETURN['ok'] = $success ? 1 : 0;
$RETURN['filename'] = $NFILENAME;
$RETURN['old_filename'] = $FILENAME;
*/

	return $RETURN;


}


















/**
 * Synchronisiert die Datei $physical_filename des Mediafolders in den
 * Medienpool
 *
 * @param $physical_filename
 * @param $category_id
 * @param $title
 * @param $filesize
 * @param $filetype
 */
function rex_mediapool_syncFile($physical_filename,$category_id,$title,$filesize = null, $filetype = null, $doSubindexing = FALSE)
{
  global $REX;

  $abs_file = $REX['MEDIAFOLDER'].'/'. $physical_filename;

  if(!file_exists($abs_file))
  {
    return false;
  }

  if(empty($filesize))
  {
    $filesize = filesize($abs_file);
  }

  if(empty($filetype) && function_exists('mime_content_type'))
  {
    $filetype = mime_content_type($abs_file);
  }

  $FILE = array();
  $FILE['name'] = $physical_filename;
  $FILE['size'] = $filesize;
  $FILE['type'] = $filetype;

  $FILEINFOS = array();
  $FILEINFOS['title'] = $title;

  $RETURN = rex_mediapool_saveMedia($FILE, $category_id, $FILEINFOS, NULL, FALSE);
  return $RETURN['ok'] == 1;
}

/**
 * Ausgabe des Medienpool Formulars
 */
function rex_mediapool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
{
  global $I18N, $REX, $subpage, $ftitle, $warning, $info;

  $s = '';

  $cats_sel = new rex_mediacategory_select();
  $cats_sel->setStyle('class="rex-form-select"');
  $cats_sel->setSize(1);
  $cats_sel->setName('rex_file_category');
  $cats_sel->setId('rex_file_category');
  $cats_sel->addOption($I18N->msg('pool_kats_no'),"0");
  $cats_sel->setAttribute('onchange', 'this.form.submit()');
  $cats_sel->setSelected($rex_file_category);

  if (isset($warning) and $warning != "")
  {
    $s .= rex_warning($warning);
    $warning = "";
  }
  
  if (isset($info) and $info != "")
  {
    $s .= rex_info($info);
    $info = "";
  }

  if (!isset($ftitle)) $ftitle = '';

  $add_file = '';
  if($file_chooser)
  {
    $devInfos = '';
    if($REX['USER']->hasPerm('advancedMode[]'))
    {
      $devInfos =
      '<span class="rex-form-notice">
         '. $I18N->msg('phpini_settings') .':<br />
         '. ((rex_ini_get('file_uploads') == 0) ? '<span>'. $I18N->msg('pool_upload') .':</span> <em>'. $I18N->msg('pool_upload_disabled') .'</em><br />' : '') .'
         <span>'. $I18N->msg('pool_max_uploadsize') .':</span> '. OOMedia::_getFormattedSize(rex_ini_get('upload_max_filesize')) .'<br />
         <span>'. $I18N->msg('pool_max_uploadtime') .':</span> '. rex_ini_get('max_input_time') .'s
       </span>';
    }

    $add_file = '
                <div class="rex-form-row">
                  <p class="rex-form-file">
                    <label for="file_new">'.$I18N->msg('pool_file_file').'</label>
                    <input class="rex-form-file" type="file" id="file_new" name="file_new" size="30" />
                    '. $devInfos .'
                  </p>
                </div>';
  }

  $arg_fields = '';
  foreach(rex_request('args', 'array') as $arg_name => $arg_value)
  {
    $arg_fields .= '<input type="hidden" name="args['. $arg_name .']" value="'. $arg_value .'" />'. "\n";
  }
  
  $arg_fields = '';
  $opener_input_field = rex_request('opener_input_field','string');
  if ($opener_input_field != '')
  {
    $arg_fields .= '<input type="hidden" name="opener_input_field" value="'. htmlspecialchars($opener_input_field) .'" />'. "\n";
  }
  
  $add_submit = '';
  if($close_form)
  {
    $add_submit = '<input type="submit" class="rex-form-submit" name="saveandexit" value="'.$I18N->msg('pool_file_upload_get').'"'. rex_accesskey($I18N->msg('pool_file_upload_get'), $REX['ACKEY']['SAVE']) .' />';
  }
  
  $s .= '
      <div class="rex-form" id="rex-form-mediapool-other">
        <form action="index.php" method="post" enctype="multipart/form-data">
          <fieldset class="rex-form-col-1">
            <legend>'. $form_title .'</legend>
            <div class="rex-form-wrapper">
              <input type="hidden" name="page" value="mediapool" />
              <input type="hidden" name="media_method" value="add_file" />
              <input type="hidden" name="subpage" value="'. $subpage .'" />
              '.$arg_fields.'
              
              <div class="rex-form-row">
                <p class="rex-form-text">
                  <label for="ftitle">'.$I18N->msg('pool_file_title').'</label>
                  <input class="rex-form-text" type="text" size="20" id="ftitle" name="ftitle" value="'.htmlspecialchars(stripslashes($ftitle)).'" />
                </p>
              </div>
              
              <div class="rex-form-row">
                <p class="rex-form-select">
                  <label for="rex_file_category">'.$I18N->msg('pool_file_category').'</label>
                  '.$cats_sel->get().'
                </p>
              </div>

              <div class="rex-clearer"></div>';

  // ----- EXTENSION POINT
  $s .= rex_register_extension_point('MEDIA_FORM_ADD', '');

  $s .=        $add_file .'
              <div class="rex-form-row">
                <p class="rex-form-submit">
                 <input class="rex-form-submit" type="submit" name="save" value="'.$button_title.'"'. rex_accesskey($button_title, $REX['ACKEY']['SAVE']) .' />
                 '. $add_submit .'
                </p>
              </div>

              <div class="rex-clearer"></div>
            </div>
          </fieldset>
        ';

  if($close_form)
  {
    $s .= '</form></div>'."\n";
  }

  return $s;
}

/**
 * Ausgabe des Medienpool Upload-Formulars
 */
function rex_mediapool_Uploadform($rex_file_category)
{
  global $I18N;

  return rex_mediapool_Mediaform($I18N->msg('pool_file_insert'), $I18N->msg('pool_file_upload'), $rex_file_category, true, true);
}

/**
 * Ausgabe des Medienpool Sync-Formulars
 */
function rex_mediapool_Syncform($rex_file_category)
{
  global $I18N;

  return rex_mediapool_Mediaform($I18N->msg('pool_sync_title'), $I18N->msg('pool_sync_button'), $rex_file_category, false, false);
}