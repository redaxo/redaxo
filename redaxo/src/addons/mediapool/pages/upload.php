<?php

// *************************************** Subpage: ADD FILE

$media_method = rex_request('media_method', 'string');

// ----- METHOD ADD FILE
if ($media_method == 'add_file') {
    global $warning;

    if (rex_post('save', 'boolean') || rex_post('saveandexit', 'boolean')) {
        if ($_FILES['file_new']['name'] != '' && $_FILES['file_new']['name'] != 'none') {
            if (!rex_mediapool_isAllowedMediaType($_FILES['file_new']['name'], rex_post('args', 'array'))) {
                $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($_FILES['file_new']['name']) . '</code>';
                $whitelist = rex_mediapool_getMediaTypeWhitelist(rex_post('args', 'array'));
                $warning .= count($whitelist) > 0
                    ? '<br />' . rex_i18n::msg('pool_file_allowed_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', $whitelist), ', ') . '</code>'
                    : '<br />' . rex_i18n::msg('pool_file_banned_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', rex_mediapool_getMediaTypeBlacklist()), ', ') . '</code>';
            } else {
                $FILEINFOS['title'] = rex_request('ftitle', 'string');

                if (!$PERMALL && !rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category)) {
                    $rex_file_category = 0;
                }

                // function in function.rex_mediapool.php
                $return = rex_mediapool_saveMedia($_FILES['file_new'], $rex_file_category, $FILEINFOS, rex::getUser()->getValue('login'));
                $info = $return['msg'];
                $subpage = '';

                // ----- EXTENSION POINT
                if ($return['ok'] == 1) {
                    rex_extension::registerPoint(new rex_extension_point('MEDIA_ADDED', '', $return));
                }

                if (rex_post('saveandexit', 'boolean') && $return['ok'] == 1) {
                    $file_name = $return['filename'];
                    $ffiletype = $return['type'];
                    $title = $return['title'];

                    if ($opener_input_field != '') {
                        if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
                            $js = "selectMedialist('" . $file_name . "');";
                            $js .= 'location.href = "' . rex_url::backendPage('mediapool', ['info' => rex_i18n::msg('pool_file_added'), 'opener_input_field' => $opener_input_field], false) . '";';
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
                    rex_response::sendRedirect(rex_url::backendPage('mediapool/media', ['info' => $info, 'opener_input_field' => $opener_input_field], false));
                } else {
                    $warning = rex_i18n::msg('pool_file_movefailed');
                }
            }
        } else {
            $warning = rex_i18n::msg('pool_file_not_found');
        }
    }
}

// ----- METHOD ADD FORM
echo rex_mediapool_Uploadform($rex_file_category);
