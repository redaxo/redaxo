<?php

// *************************************** Subpage: ADD FILE

$media_method = rex_request('media_method', 'string');

// ----- METHOD ADD FILE
if ($media_method == 'add_file') {
  if (rex_post('save', 'boolean') || rex_post('saveandexit', 'boolean')) {
    if ($_FILES['file_new']['name'] != '' && $_FILES['file_new']['name'] != 'none') {
      $FILEINFOS['title'] = rex_request('ftitle', 'string');

      if (!$PERMALL && !rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category))
        $rex_file_category = 0;

      // function in function.rex_mediapool.inc.php
      $return = rex_mediapool_saveMedia($_FILES['file_new'], $rex_file_category, $FILEINFOS, rex::getUser()->getValue('login'));
      $info = $return['msg'];
      $subpage = '';

      // ----- EXTENSION POINT
      if ($return['ok'] == 1)
        rex_extension::registerPoint('MEDIA_ADDED', '', $return);

      if (rex_post('saveandexit', 'boolean') && $return['ok'] == 1) {
        $file_name = $return['filename'];
        $ffiletype = $return['type'];
        $title = $return['title'];

        if ($opener_input_field == 'TINYIMG') {
          if (rex_media::_isImage($file_name)) {
            $js = "insertImage('$file_name','$title');";
          }
        } elseif ($opener_input_field == 'TINY') {
            $js = "insertLink('" . $file_name . "');";
        } elseif ($opener_input_field != '') {
          if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
            $js = "selectMedialist('" . $file_name . "');";
            $js .= 'location.href = "index.php?page=mediapool&info=' . urlencode(rex_i18n::msg('pool_file_added')) . '&opener_input_field=' . $opener_input_field . '";';
          } else {
            $js = "selectMedia('" . $file_name . "');";
          }
        }

        echo "<script language=javascript>\n";
        echo $js;
        // echo "\nself.close();\n";
        echo '</script>';
        exit;
      } elseif ($return['ok'] == 1) {
        header('Location:index.php?page=mediapool&info=' . urlencode(rex_i18n::msg('pool_file_added')) . '&opener_input_field=' . $opener_input_field);
        exit;
      } else {
        $warning = rex_i18n::msg('pool_file_movefailed');
      }

    } else {
      $warning = rex_i18n::msg('pool_file_not_found');
    }
  }
}

// ----- METHOD ADD FORM
echo rex_mediapool_Uploadform($rex_file_category);
