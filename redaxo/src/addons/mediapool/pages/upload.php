<?php

assert(isset($PERMALL) && is_bool($PERMALL));
assert(isset($opener_input_field) && is_string($opener_input_field));

if (!isset($rex_file_category)) {
    $rex_file_category = 0;
}

// *************************************** Subpage: ADD FILE

$media_method = rex_request('media_method', 'string');
$csrf = rex_csrf_token::factory('mediapool');

// ----- METHOD ADD FILE
if ('add_file' == $media_method) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        global $warning;
        if (rex_post('save', 'boolean') || rex_post('saveandexit', 'boolean')) {
            if ('' != $_FILES['file_new']['name'] && 'none' != $_FILES['file_new']['name']) {
                if (!rex_mediapool_isAllowedMediaType($_FILES['file_new']['name'], rex_post('args', 'array'))) {
                    $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($_FILES['file_new']['name']) . '</code>';
                    $whitelist = rex_mediapool_getMediaTypeWhitelist(rex_post('args', 'array'));
                    $warning .= count($whitelist) > 0
                        ? '<br />' . rex_i18n::msg('pool_file_allowed_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', $whitelist), ', ') . '</code>'
                        : '<br />' . rex_i18n::msg('pool_file_banned_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', rex_mediapool_getMediaTypeBlacklist()), ', ') . '</code>';
                } elseif (!rex_mediapool_isAllowedMimeType($_FILES['file_new']['tmp_name'], $_FILES['file_new']['name'])) {
                    $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($_FILES['file_new']['name']) . '</code> (<code>' . rex_file::mimeType($_FILES['file_new']['tmp_name']) . '</code>)';
                } else {
                    $FILEINFOS = [];
                    $FILEINFOS['title'] = rex_request('ftitle', 'string');

                    if (!$PERMALL && !rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category)) {
                        $rex_file_category = 0;
                    }

                    // function in function.rex_mediapool.php
                    $return = rex_mediapool_saveMedia($_FILES['file_new'], $rex_file_category, $FILEINFOS, rex::getUser()->getValue('login'));
                    $info = $return['msg'];
                    $subpage = '';

                    if (rex_post('saveandexit', 'boolean') && 1 == $return['ok']) {
                        $file_name = $return['filename'];
                        $ffiletype = $return['type'];
                        $title = $return['title'];

                        if ('' != $opener_input_field) {
                            if ('REX_MEDIALIST_' == substr($opener_input_field, 0, 14)) {
                                $js = "selectMedialist('" . $file_name . "');";
                                $js .= 'location.href = "' . rex_url::backendPage('mediapool', ['info' => rex_i18n::msg('pool_file_added'), 'opener_input_field' => $opener_input_field], false) . '";';
                            } else {
                                $js = "selectMedia('" . $file_name . "');";
                            }
                        }

                        echo "<script language=javascript>\n";

                        if (isset($js)) {
                            echo $js;
                        }
                        // echo "\nself.close();\n";
                        echo '</script>';
                        exit;
                    }
                    if (1 == $return['ok']) {
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
}

// ----- METHOD ADD FORM
echo rex_mediapool_Uploadform($rex_file_category);
