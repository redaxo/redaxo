<?php

assert(isset($PERMALL) && is_bool($PERMALL));
assert(isset($openerInputField) && is_string($openerInputField));

if (!isset($rexFileCategory)) {
    $rexFileCategory = 0;
}

if (!$PERMALL && !rex::requireUser()->getComplexPerm('media')->hasCategoryPerm($rexFileCategory)) {
    $rexFileCategory = 0;
}

$mediaMethod = rex_request('media_method', 'string');
$csrf = rex_csrf_token::factory('mediapool');

if ('add_file' == $mediaMethod) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        global $warning;
        if (rex_post('save', 'boolean') || rex_post('saveandexit', 'boolean')) {
            $data = [];
            $data['title'] = rex_request('ftitle', 'string');
            $data['category_id'] = (int) $rexFileCategory;
            $data['file'] = rex_files('file_new', [
                ['name', 'string'],
                ['tmp_name', 'string'],
                ['error', 'int'],
            ]);

            try {
                $data = rex_media_service::addMedia($data, true, rex_post('args', 'array'));
                $info = rex_i18n::msg('pool_file_added');
                if (rex_post('saveandexit', 'boolean')) {
                    if ('' != $openerInputField) {
                        if (str_starts_with($openerInputField, 'REX_MEDIALIST_')) {
                            $js = "selectMedialist('" . $data['filename'] . "');";
                            $js .= 'location.href = "' . rex_url::backendPage('mediapool', ['info' => $info, 'opener_input_field' => $openerInputField], false) . '";';
                        } else {
                            $js = "selectMedia('" . $data['filename'] . "');";
                        }
                    }

                    echo '<script type="text/javascript" nonce="' . rex_response::getNonce() . '">';
                    if (isset($js)) {
                        echo $js;
                    }
                    echo '</script>';
                    exit;
                }

                rex_response::sendRedirect(rex_url::backendPage('mediapool/media', ['info' => $info, 'opener_input_field' => $openerInputField], false));
            } catch (rex_api_exception $e) {
                $warning = $e->getMessage();
            }
        }
    }
}

echo rex_mediapool_Uploadform($rexFileCategory);
