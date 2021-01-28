<?php

/**
 * Funktionensammlung für den Medienpool.
 *
 * @package redaxo\mediapool
 */

/**
 * Ausgabe des Medienpool Formulars.
 *
 * @return string
 */
function rex_mediapool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
{
    global $ftitle, $warning, $info;

    $categories = $params['categories'] ?? [];

    $s = '';

    $cats_sel = new rex_media_category_select();
    $cats_sel->setStyle('class="form-control"');
    $cats_sel->setSize(1);
    $cats_sel->setMultiple();
    $cats_sel->setName('rex_media_categories');
    $cats_sel->setId('rex-mediapool-category');
    $cats_sel->setAttribute('class', 'selectpicker form-control');
    $cats_sel->setAttribute('data-live-search', 'true');
    $cats_sel->setAttribute('onchange', 'this.form.submit()');

    foreach ($categories as $category) {
        $cats_sel->setSelected($category);
    }

    $tags = $params['tags'] ?? '';

    if (isset($warning)) {
        if (is_array($warning)) {
            if (count($warning) > 0) {
                $s .= rex_view::error(implode('<br />', $warning));
            }
        } elseif ('' != $warning) {
            $s .= rex_view::error($warning);
        }
        $warning = '';
    }

    if (isset($info)) {
        if (is_array($info)) {
            if (count($info) > 0) {
                $s .= rex_view::success(implode('<br />', $info));
            }
        } elseif ('' != $info) {
            $s .= rex_view::success($info);
        }
        $info = '';
    }

    if (!isset($ftitle)) {
        $ftitle = '';
    }

    $arg_fields = '';
    foreach (rex_request('args', 'array') as $arg_name => $arg_value) {
        $arg_fields .= '<input type="hidden" name="args[' . rex_escape($arg_name) . ']" value="' . rex_escape($arg_value) . '" />' . "\n";
    }

    $opener_input_field = rex_request('opener_input_field', 'string');
    if ('' != $opener_input_field) {
        $arg_fields .= '<input class="form-control" type="hidden" name="opener_input_field" value="' . rex_escape($opener_input_field) . '" />' . "\n";
    }

    $add_submit = '';
    if ($close_form && '' != $opener_input_field) {
        $add_submit = '<button class="btn btn-save" type="submit" name="saveandexit" value="' . rex_i18n::msg('pool_file_upload_get') . '"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('pool_file_upload_get') . '</button>';
    }

    $panel = '';
    $panel .= '<fieldset>';
    $formElements = [];

    $e = [];
    $e['label'] = '<label for="rex-mediapool-categories">' . rex_i18n::msg('pool_media_categories') . '</label>';
    $e['field'] = $cats_sel->get();
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label for="rex-mediapool-tags">' . rex_i18n::msg('pool_media_tags') . '</label>';
    $e['field'] = '<input type="text" name="rex_media_tags" value="'.rex_escape($tags).'" />';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label for="rex-mediapool-title">' . rex_i18n::msg('pool_file_title') . '</label>';
    $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . rex_escape($ftitle) . '" />';
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_FORM_ADD', ''));

    if ($file_chooser) {
        $e = [];
        $e['label'] = '<label for="rex-mediapool-choose-file">' . rex_i18n::msg('pool_file_file') . '</label>';
        $e['field'] = '<input id="rex-mediapool-choose-file" type="file" name="file_new" />';
        $e['after'] = '<h3>' . rex_i18n::msg('phpini_settings') . '</h3>
                        <dl class="dl-horizontal text-left">
                        ' . ((0 == rex_ini_get('file_uploads')) ? '<dt><span class="text-warning">' . rex_i18n::msg('pool_upload') . '</span></dt><dd><span class="text-warning">' . rex_i18n::msg('pool_upload_disabled') . '</span></dd>' : '') . '
                            <dt>' . rex_i18n::msg('pool_max_uploadsize') . ':</dt><dd>' . rex_formatter::bytes(rex_ini_get('upload_max_filesize')) . '</dd>
                            <dt>' . rex_i18n::msg('pool_max_uploadtime') . ':</dt><dd>' . rex_ini_get('max_input_time') . 's</dd>
                        </dl>';

        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        $panel .= $fragment->parse('core/form/form.php');
    }
    $panel .= '</fieldset>';

    $formElements = [];

    $e = [];
    $e['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $button_title . '"' . rex::getAccesskey($button_title, 'save') . '>' . $button_title . '</button>';
    $formElements[] = $e;

    $e = [];
    $e['field'] = $add_submit;
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $form_title, false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    $s .= ' <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
                ' . rex_csrf_token::factory('mediapool')->getHiddenField() . '
                <fieldset>
                    <input type="hidden" name="media_method" value="add_file" />
                    ' . $arg_fields . '
                    ' . $content . '
                </fieldset>
            ';

    if ($close_form) {
        $s .= '</form>' . "\n";
    }

    return $s;
}

/**
 * Ausgabe des Medienpool Upload-Formulars.
 */
function rex_mediapool_Uploadform($params)
{
    return rex_mediapool_Mediaform(rex_i18n::msg('pool_file_insert'), rex_i18n::msg('pool_file_upload'), $params, true, true);
}

/**
 * Ausgabe des Medienpool Sync-Formulars.
 */
function rex_mediapool_Syncform($params)
{
    return rex_mediapool_Mediaform(rex_i18n::msg('pool_sync_title'), rex_i18n::msg('pool_sync_button'), $params, false, false);
}
