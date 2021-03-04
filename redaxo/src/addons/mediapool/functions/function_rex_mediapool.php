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
function rex_mediapool_Mediaform($formTitle, $buttonTitle, $rexFileCategory, $fileChooser, $closeForm)
{
    global $ftitle, $warning, $info;

    $categories = $params['categories'] ?? [];

    $s = '';

    $catsSel = new rex_media_category_select();
    $catsSel->setStyle('class="form-control"');
    $catsSel->setSize(1);
    $catsSel->setMultiple();
    $catsSel->setName('rex_media_categories');
    $catsSel->setId('rex-mediapool-category');
    $catsSel->setAttribute('class', 'selectpicker form-control');
    $catsSel->setAttribute('data-live-search', 'true');
    $catsSel->setAttribute('onchange', 'this.form.submit()');

    foreach ($categories as $category) {
        $catsSel->setSelected($category);
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

    $argFields = '';
    foreach (rex_request('args', 'array') as $argName => $argValue) {
        $argFields .= '<input type="hidden" name="args[' . rex_escape($argName) . ']" value="' . rex_escape($argValue) . '" />' . "\n";
    }

    $openerInputField = rex_request('opener_input_field', 'string');
    if ('' != $openerInputField) {
        $argFields .= '<input class="form-control" type="hidden" name="opener_input_field" value="' . rex_escape($openerInputField) . '" />' . "\n";
    }

    $addSubmit = '';
    if ($closeForm && '' != $openerInputField) {
        $addSubmit = '<button class="btn btn-save" type="submit" name="saveandexit" value="' . rex_i18n::msg('pool_file_upload_get') . '"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('pool_file_upload_get') . '</button>';
    }

    $panel = '';
    $panel .= '<fieldset>';
    $formElements = [];

    $e = [];
    $e['label'] = '<label for="rex-mediapool-categories">' . rex_i18n::msg('pool_media_categories') . '</label>';
    $e['field'] = $catsSel->get();
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

    if ($fileChooser) {
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
    $e['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $buttonTitle . '"' . rex::getAccesskey($buttonTitle, 'save') . '>' . $buttonTitle . '</button>';
    $formElements[] = $e;

    $e = [];
    $e['field'] = $addSubmit;
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formTitle, false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    $s .= ' <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
                ' . rex_csrf_token::factory('mediapool')->getHiddenField() . '
                <fieldset>
                    <input type="hidden" name="media_method" value="add_file" />
                    ' . $argFields . '
                    ' . $content . '
                </fieldset>
            ';

    if ($closeForm) {
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
