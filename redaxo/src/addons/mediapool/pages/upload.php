<?php

assert(isset($opener_input_field) && is_string($opener_input_field));

$media_method = rex_request('media_method', 'string');
$opener_input_field = rex_request('opener_input_field', 'string');
$csrf = rex_csrf_token::factory('mediapool');

$mediaForm = new rex_media_form();
$mediaForm->setTitle(rex_i18n::msg('pool_file_insert'));
$mediaForm->setButtonTitle(rex_i18n::msg('pool_file_upload'));

$mediaForm->addField([
    'label' => '',
    'field' => $csrf->getHiddenField(),
]);

$mediaForm->addField([
    'label' => '',
    'field' => '<input type="hidden" name="media_method" value="add_file">',
]);

foreach (rex_request('args', 'array') as $arg_name => $arg_value) {
    $mediaForm->addField([
        'label' => '',
        'field' => '<input type="hidden" name="args[' . rex_escape($arg_name) . ']" value="' . rex_escape($arg_value) . '" />',
    ]);
}

if ('' != $opener_input_field) {
    $mediaForm->addField([
        'label' => '',
        'field' => '<input class="form-control" type="hidden" name="opener_input_field" value="' . rex_escape($opener_input_field) . '" />',
    ]);
}

$data = [];

if ('add_file' == $media_method) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $data = [];
        $data['file'] = [];

        $data['file']['name'] = $_FILES['rex_media_file']['name'] ?? '';
        $data['file']['path'] = $_FILES['rex_media_file']['tmp_name'] ?? '';
        $data['file']['type'] = $_FILES['rex_media_file']['type'] ?? '';
        $data['file']['size'] = $_FILES['rex_media_file']['size'] ?? 0;
        $data['title'] = rex_request('rex_media_title', 'string');
        $data['categories'] = rex_request('rex_media_categories', 'array');
        $data['tags'] = rex_request('rex_media_tags', 'string');
        $data['status'] = rex_request('rex_media_status', 'int');

        $args = rex_post('args', 'array');
        $whitelist_types = is_array(@$args['types']) ? $args['types'] : [];

        try {
            $message = new rex_api_result(
                true,
                rex_media_service::addMedia($data, rex::getUser()->getValue('login'), true, $whitelist_types)
            );

            dump($message);

            echo rex_view::success($message->getMessage());

            /*
            if (rex_post('saveandexit', 'boolean') && 1 == $return['ok']) {

                // TODO:
                // wie bekomme ich den Filenam zurück für die Übergabe

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
            */
        } catch (Exception $exception) {
            echo rex_view::error($exception->getMessage());
        }
    }
}

$add_submit = '';
if ('' != $opener_input_field) {
    $mediaForm->addSubmit(
        '<button class="btn btn-save" type="submit" name="saveandexit" value="' . rex_i18n::msg('pool_file_upload_get') . '"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('pool_file_upload_get') . '</button>'
    );
}

$mediaForm->setData($data);
$mediaForm->setfileSelection();

echo $mediaForm->get();
