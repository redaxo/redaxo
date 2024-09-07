<?php

use Redaxo\Core\Core;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Form\Select\MediaCategorySelect;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Security\CsrfToken;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Formatter;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\Message;

/**
 * Ausgabe des Medienpool Formulars.
 *
 * @return string
 */
function rex_mediapool_Mediaform($formTitle, $buttonTitle, $rexFileCategory, $fileChooser, $closeForm)
{
    global $ftitle, $warning, $info;

    $s = '';

    $catsSel = new MediaCategorySelect();
    $catsSel->setStyle('class="form-control"');
    $catsSel->setSize(1);
    $catsSel->setName('rex_file_category');
    $catsSel->setId('rex-mediapool-category');
    $catsSel->setAttribute('class', 'selectpicker form-control');
    $catsSel->setAttribute('data-live-search', 'true');
    $catsSel->setAttribute('onchange', 'this.form.submit()');
    $catsSel->setSelected($rexFileCategory);

    if (Core::requireUser()->getComplexPerm('media')->hasAll()) {
        $catsSel->addOption(I18n::msg('pool_kats_no'), '0');
    }

    if (isset($warning)) {
        if (is_array($warning)) {
            if (count($warning) > 0) {
                $s .= Message::error(implode('<br />', $warning));
            }
        } elseif ('' != $warning) {
            $s .= Message::error($warning);
        }
        $warning = '';
    }

    if (isset($info)) {
        if (is_array($info)) {
            if (count($info) > 0) {
                $s .= Message::success(implode('<br />', $info));
            }
        } elseif ('' != $info) {
            $s .= Message::success($info);
        }
        $info = '';
    }

    if (!isset($ftitle)) {
        $ftitle = '';
    }

    $argFields = '';
    foreach (Request::request('args', 'array') as $argName => $argValue) {
        $argFields .= '<input type="hidden" name="args[' . rex_escape($argName) . ']" value="' . rex_escape($argValue) . '" />' . "\n";
    }

    $openerInputField = Request::request('opener_input_field', 'string');
    if ('' != $openerInputField) {
        $argFields .= '<input type="hidden" name="opener_input_field" value="' . rex_escape($openerInputField) . '" />' . "\n";
    }

    $addSubmit = '';
    if ($closeForm && '' != $openerInputField) {
        $addSubmit = '<button class="btn btn-save" type="submit" name="saveandexit" value="' . I18n::msg('pool_file_upload_get') . '"' . Core::getAccesskey(I18n::msg('save_and_close_tooltip'), 'save') . '>' . I18n::msg('pool_file_upload_get') . '</button>';
    }

    $panel = '';
    $panel .= '<fieldset>';
    $formElements = [];

    $e = [];
    $e['label'] = '<label for="rex-mediapool-category">' . I18n::msg('pool_file_category') . '</label>';
    $e['field'] = $catsSel->get();
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label for="rex-mediapool-title">' . I18n::msg('pool_file_title') . '</label>';
    $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . rex_escape($ftitle) . '" maxlength="255" />';
    $formElements[] = $e;

    $fragment = new Fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= Extension::registerPoint(new ExtensionPoint('MEDIA_FORM_ADD', ''));

    if ($fileChooser) {
        $e = [];
        $e['label'] = '<label for="rex-mediapool-choose-file">' . I18n::msg('pool_file_file') . '</label>';
        $e['field'] = '<input id="rex-mediapool-choose-file" type="file" name="file_new" />';
        $e['after'] = '<h3>' . I18n::msg('phpini_settings') . '</h3>
                        <dl class="dl-horizontal text-left">
                        ' . ((0 == rex_ini_get('file_uploads')) ? '<dt><span class="text-warning">' . I18n::msg('pool_upload') . '</span></dt><dd><span class="text-warning">' . I18n::msg('pool_upload_disabled') . '</span></dd>' : '') . '
                            <dt>' . I18n::msg('pool_max_uploadsize') . ':</dt><dd>' . Formatter::bytes(rex_ini_get('upload_max_filesize')) . '</dd>
                            <dt>' . I18n::msg('pool_max_uploadtime') . ':</dt><dd>' . rex_ini_get('max_input_time') . 's</dd>
                        </dl>';

        $fragment = new Fragment();
        $fragment->setVar('elements', [$e], false);
        $panel .= $fragment->parse('core/form/form.php');
    }
    $panel .= '</fieldset>';

    $formElements = [];

    $e = [];
    $e['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $buttonTitle . '"' . Core::getAccesskey($buttonTitle, 'save') . '>' . $buttonTitle . '</button>';
    $formElements[] = $e;

    $e = [];
    $e['field'] = $addSubmit;
    $formElements[] = $e;

    $fragment = new Fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new Fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formTitle, false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    $s .= ' <form action="' . Url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
                ' . CsrfToken::factory('mediapool')->getHiddenField() . '
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
 * @return string
 */
function rex_mediapool_Uploadform($rexFileCategory)
{
    return rex_mediapool_Mediaform(I18n::msg('pool_file_insert'), I18n::msg('pool_file_upload'), $rexFileCategory, true, true);
}

/**
 * Ausgabe des Medienpool Sync-Formulars.
 * @return string
 */
function rex_mediapool_Syncform($rexFileCategory)
{
    return rex_mediapool_Mediaform(I18n::msg('pool_sync_title'), I18n::msg('pool_sync_button'), $rexFileCategory, false, false);
}