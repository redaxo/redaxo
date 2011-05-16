<?php

/**
 * Funktionensammlung für den Medienpool
 *
 * @package redaxo5
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
    if (file_exists(rex_path::media($NFILENAME)))
    {
      $cnt = 1;
      while(file_exists(rex_path::media($NFILE_NAME.'_'.$cnt.$NFILE_EXT)))
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

  global $REX;

  $rex_file_category = (int) $rex_file_category;

  $gc = rex_sql::factory();
  $gc->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'media_category WHERE id='. $rex_file_category);
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
  $srcFile = rex_path::media($FILENAME);
  $dstFile = rex_path::media($NFILENAME);

  $success = true;
  if($isFileUpload) // Fileupload?
  {
    if(!@move_uploaded_file($FILE['tmp_name'],$dstFile))
    {
      $message .= rex_i18n::msg("pool_file_movefailed");
      $success = false;
    }
  }
  else // Filesync?
  {
    if(!@rename($srcFile,$dstFile))
    {
      $message .= rex_i18n::msg("pool_file_movefailed");
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
    $FILESQL->setTable($REX['TABLE_PREFIX'].'media');
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

    $message .= rex_i18n::msg("pool_file_added");

    rex_media_cache::deleteList($rex_file_category);
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
  rex_extension::registerPoint('MEDIA_ADDED','',$RETURN);

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

  global $REX;

  $RETURN = array();

  $FILESQL = rex_sql::factory();
  // $FILESQL->debugsql = 1;
  $FILESQL->setTable($REX['TABLE_PREFIX'].'media');
  $FILESQL->setWhere('media_id='. $FILEINFOS["file_id"]);
  $FILESQL->setValue('title',$FILEINFOS["title"]);
  $FILESQL->setValue('category_id',$FILEINFOS["rex_file_category"]);

  $msg = '';

  $updated = false;
  if ($_FILES['file_new']['name'] != '' && $_FILES['file_new']['name'] != 'none')
  {
    $ffilename = $_FILES['file_new']['tmp_name'];
    $ffiletype = $_FILES['file_new']['type'];
    $ffilesize = $_FILES['file_new']['size'];

    $p_new = pathinfo($_FILES['file_new']['name']);
    $p_old = pathinfo($FILEINFOS["filename"]);

    // if ($ffiletype == $FILEINFOS["filetype"] || rex_ooMedia::compareImageTypes($ffiletype,$FILEINFOS["filetype"]))
    if($p_new['extension'] == $p_old['extension'])
    {
      if (move_uploaded_file($ffilename, rex_path::media($FILEINFOS["filename"])) ||
      copy($ffilename, rex_path::media($FILEINFOS["filename"])))
      {
        $RETURN["msg"] = rex_i18n::msg('pool_file_changed');
        $FILEINFOS["filetype"] = $ffiletype;
        $FILEINFOS["filesize"] = $ffilesize;

        $FILESQL->setValue('filetype',$FILEINFOS["filetype"]);
        // $FILESQL->setValue('originalname',$ffilename);
        $FILESQL->setValue('filesize',$FILEINFOS["filesize"]);
        if($size = @getimagesize(rex_path::media($FILEINFOS["filename"])))
        {
          $FILESQL->setValue('width',$size[0]);
          $FILESQL->setValue('height',$size[1]);
        }
        @chmod(rex_path::media($FILEINFOS["filename"]), $REX['FILEPERM']);
        $updated = true;
      }else
      {
        $RETURN["msg"] = rex_i18n::msg('pool_file_upload_error');
      }
    }else
    {
      $RETURN["msg"] = rex_i18n::msg('pool_file_upload_errortype');
    }
  }

  // Aus BC gruenden hier mit int 1/0
  $RETURN["ok"] = $updated ? 1 : 0;
  if(!isset($RETURN["msg"]))
  {
    $RETURN["msg"] = rex_i18n::msg('pool_file_infos_updated');
    $RETURN["ok"] = 1;
  }
  if($RETURN['ok'] == 1)
  {
    $RETURN["filename"] = $FILEINFOS["filename"];
    $RETURN["filetype"] = $FILEINFOS["filetype"];
    $RETURN["media_id"] = $FILEINFOS["file_id"];
  }

  $FILESQL->addGlobalUpdateFields();
  $FILESQL->update();

  rex_media_cache::delete($FILEINFOS["filename"]);


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

  $abs_file = rex_path::media($physical_filename);

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

  if(empty($filetype) && function_exists('finfo_open'))
  {
    $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
    $filetype = finfo_file($finfo, $abs_file);
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
function rex_mediapool_Mediaform($p)
{
  global $REX;

  /*
   $form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
   , $subpage, $ftitle, $warning, $info
   */


  $s = '';

  $cats_sel = new rex_mediacategory_select();
  $cats_sel->setStyle('class="rex-form-select"');
  $cats_sel->setSize(1);
  $cats_sel->setName('rex_file_category');
  $cats_sel->setId('rex_file_category');
  $cats_sel->addOption(rex_i18n::msg('pool_kats_no'),"0");
  $cats_sel->setAttribute('onchange', 'this.form.submit()');
  $cats_sel->setSelected($p["rex_file_category"]);

  if (isset($p["warning"]) and $p["warning"] != "")
  {
    $s .= rex_warning($p["warning"]);
    $p["warning"] = "";
  }

  if (isset($p['info']) and $p['info'] != "")
  {
    $s .= rex_info($p['info']);
    $p['info'] = "";
  }

  if (!isset($p['ftitle'])) $p['ftitle'] = '';

  $add_file = '';
  if($p['file_chooser'])
  {
    $devInfos = '';
    if($REX['USER']->hasPerm('advancedMode[]'))
    {
      $devInfos =
      '<span class="rex-form-notice">
         '. rex_i18n::msg('phpini_settings') .':<br />
         '. ((rex_ini_get('file_uploads') == 0) ? '<span>'. rex_i18n::msg('pool_upload') .':</span> <em>'. rex_i18n::msg('pool_upload_disabled') .'</em><br />' : '') .'
         <span>'. rex_i18n::msg('pool_max_uploadsize') .':</span> '. rex_file::formattedSize(rex_ini_get('upload_max_filesize')) .'<br />
         <span>'. rex_i18n::msg('pool_max_uploadtime') .':</span> '. rex_ini_get('max_input_time') .'s
       </span>';
    }

    $add_file = '
                <div class="rex-form-row">
                  <p class="rex-form-file">
                    <label for="file_new">'.rex_i18n::msg('pool_file_file').'</label>
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
  if($p['close_form'] && $opener_input_field != '')
  {
    $add_submit = '<input type="submit" class="rex-form-submit" name="saveandexit" value="'.rex_i18n::msg('pool_file_upload_get').'" />';
  }

  $s .= '
      <div class="rex-form" id="rex-form-mediapool-other">
        <form action="index.php" method="post" enctype="multipart/form-data">
          <fieldset class="rex-form-col-1">
            <legend>'. $p['form_title'] .'</legend>
            <div class="rex-form-wrapper">
              <input type="hidden" name="page" value="mediapool" />
              <input type="hidden" name="media_method" value="add_file" />
              <input type="hidden" name="subpage" value="'. $p['subpage'] .'" />
              '.$arg_fields.'

              <div class="rex-form-row">
                <p class="rex-form-text">
                  <label for="ftitle">'.rex_i18n::msg('pool_file_title').'</label>
                  <input class="rex-form-text" type="text" size="20" id="ftitle" name="ftitle" value="'.htmlspecialchars($p['ftitle']).'" />
                </p>
              </div>

              <div class="rex-form-row">
                <p class="rex-form-select">
                  <label for="rex_file_category">'.rex_i18n::msg('pool_file_category').'</label>
                  '.$cats_sel->get().'
                </p>
              </div>

              <div class="rex-clearer"></div>';

  // ----- EXTENSION POINT
  $s .= rex_extension::registerPoint('MEDIA_FORM_ADD', '');

  $s .=        $add_file .'
              <div class="rex-form-row">
                <p class="rex-form-submit">
                 <input class="rex-form-submit" type="submit" name="save" value="'.$p['button_title'].'" />
                 '. $add_submit .'
                </p>
              </div>

              <div class="rex-clearer"></div>
            </div>
          </fieldset>
        ';

  if($p['close_form'])
  {
    $s .= '</form></div>'."\n";
  }

  return $s;
}

/**
 * Fügt die benötigen Assets ein
 *
 * @param $params Extension-Point Parameter
 */
function rex_mediapool_add_assets($params)
{
  $params['subject'] .= "\n  ".
    '<script type="text/javascript" src="'. rex_path::addonAssets('mediapool', 'mediapool.js', rex_path::RELATIVE) .'"></script>';

  return $params['subject'];
}