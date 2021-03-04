<?php

assert(isset($opener_input_field) && is_string($opener_input_field));

$media_method = rex_request('media_method', 'string');
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
        dump($_FILES);
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



            dump($data);

            $data = rex_media_service::addMedia($data, rex::getUser()->getValue('login'), true, $whitelist_types);

            echo rex_view::success($data['message']);

            if (rex_post('saveandexit', 'boolean')) {
                if ('' != $opener_input_field) {
                    if ('REX_MEDIALIST_' == substr($opener_input_field, 0, 14)) {
                        $js = "selectMedialist('" . $data['file']['name_new'] . "');";
                        $js .= 'location.href = "' . rex_url::backendPage('mediapool', ['info' => rex_i18n::msg('pool_file_added'), 'opener_input_field' => $opener_input_field], false) . '";';
                    } else {
                        $js = "selectMedia('" . $data['file']['name_new'] . "');";
                    }
                    echo "<script language=javascript>\n";
                    echo $js;
                    echo '</script>';
                    return;
                }
            }

            rex_response::sendRedirect(rex_url::backendPage('mediapool/media', ['info' => $data['message'], 'opener_input_field' => $opener_input_field], false));
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
