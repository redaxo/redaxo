<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Http\Response;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\Message;

use function Redaxo\Core\View\escape;

assert(isset($markdown) && is_callable($markdown));

$package = Addon::get('install');

$addonkey = Request::request('addonkey', 'string');
$addons = [];

echo ApiFunction::getMessage();

try {
    $addons = rex_install_packages::getMyPackages();
} catch (rex_functional_exception $e) {
    echo Message::error($e->getMessage());
    $addonkey = '';
}

if ($addonkey && isset($addons[$addonkey])) {
    $addon = $addons[$addonkey];
    $fileId = Request::request('file', 'string');

    if ($fileId) {
        $new = 'new' == $fileId;
        $file = $new ? ['version' => '', 'description' => '', 'status' => 1] : $addon['files'][(int) $fileId];

        $newVersion = Addon::get($addonkey)->getVersion();

        $uploadCheckboxDisabled = '';
        $hiddenField = '';
        if ($new || !Addon::exists($addonkey)) {
            $uploadCheckboxDisabled = ' disabled="disabled"';
            $hiddenField = '<input type="hidden" name="upload[upload_file]" value="' . ((int) $new) . '" />';
        }

        $panel = '<fieldset>';

        $formElements = [];

        $n = [];
        $n['label'] = '<label for="rex-js-install-packages-upload-version">' . $package->i18n('version') . '</label>';
        $n['field'] = '<p class="form-control-static" id="rex-js-install-packages-upload-version">' . escape($new ? $newVersion : $file['version']) . '</p>
                           <input type="hidden" name="upload[oldversion]" value="' . escape($file['version']) . '" />';
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="rex-install-packages-upload-description">' . $package->i18n('description') . '</label>';
        $n['field'] = '<textarea class="form-control" id="rex-install-packages-upload-description" name="upload[description]" rows="15">' . escape($file['description']) . '</textarea>';
        $formElements[] = $n;

        $fragment = new Fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $formElements = [];

        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label for="rex-install-packages-upload-status">' . $package->i18n('online') . '</label>';
        $n['field'] = '<input id="rex-install-packages-upload-status" type="checkbox" name="upload[status]" value="1" ' . (!$new && $file['status'] ? 'checked="checked" ' : '') . '/>';
        $formElements[] = $n;

        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label for="rex-js-install-packages-upload-upload-file">' . $package->i18n('upload_file') . '</label>' . $hiddenField;
        $n['field'] = '<input id="rex-js-install-packages-upload-upload-file" type="checkbox" name="upload[upload_file]" value="1" ' . ($new ? 'checked="checked" ' : '') . $uploadCheckboxDisabled . '/>';
        $formElements[] = $n;

        if (Addon::get($addonkey)->isInstalled() && is_dir(Url::addonAssets($addonkey))) {
            $n = [];
            $n['reverse'] = true;
            $n['label'] = '<label for="rex-js-install-packages-upload-replace-assets">' . $package->i18n('replace_assets') . '</label>';
            $n['field'] = '<input id="rex-js-install-packages-upload-replace-assets" type="checkbox" name="upload[replace_assets]" value="1" ' . ($new ? '' : 'disabled="disabled" ') . '/>';
            $formElements[] = $n;
        }

        if (is_dir(Path::addon($addonkey, 'tests'))) {
            $n = [];
            $n['reverse'] = true;
            $n['label'] = '<label for="rex-js-install-packages-upload-ignore-tests">' . $package->i18n('ignore_tests') . '</label>';
            $n['field'] = '<input id="rex-js-install-packages-upload-ignore-tests" type="checkbox" name="upload[ignore_tests]" value="1" checked="checked"' . ($new ? '' : 'disabled="disabled" ') . '/>';
            $formElements[] = $n;
        }

        $fragment = new Fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/checkbox.php');

        $panel .= '</fieldset>';

        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-abort" href="' . Url::currentBackendPage() . '">' . I18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="upload[send]" value="' . $package->i18n('send') . '">' . $package->i18n('send') . '</button>';
        $formElements[] = $n;

        if (!$new) {
            $n = [];
            $n['field'] = '<button class="btn btn-delete" value="' . $package->i18n('delete') . '" onclick="if(confirm(\'' . $package->i18n('delete') . ' ?\')) location.href=\'' . Url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId] + rex_api_install_package_delete::getUrlParams()) . '\'; else return false;">' . $package->i18n('delete') . '</button>';
            $formElements[] = $n;
        }

        $fragment = new Fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');

        $panel .= '</fieldset>';

        $fragment = new Fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', escape($addonkey) . ' <small>' . $package->i18n($new ? 'file_add' : 'file_edit') . '</small>', false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('buttons', $buttons, false);
        $content = $fragment->parse('core/page/section.php');

        $content = '
            <form action="' . Url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId] + rex_api_install_package_upload::getUrlParams()) . '" method="post">
                ' . $content . '
            </form>';
        echo $content;

        if (!$new) {
            echo '
    <script type="text/javascript" nonce="' . Response::getNonce() . '"><!--

        jQuery(function($) {
            $("#rex-js-install-packages-upload-upload-file").change(function(){
                if($(this).is(":checked"))
                {
                    ' . ($newVersion != $file['version'] ? '$("#rex-js-install-packages-upload-version").html(\'<del class="rex-package-old-version">' . $file['version'] . '</del> <ins class="rex-package-new-version">' . escape($newVersion, 'js') . '</ins>\');' : '') . '
                    $("#rex-js-install-packages-upload-replace-assets, #rex-js-install-packages-upload-ignore-tests").removeAttr("disabled");
                }
                else
                {
                    $("#rex-js-install-packages-upload-version").html("' . escape($file['version'], 'js') . '");
                    $("#rex-js-install-packages-upload-replace-assets, #rex-js-install-packages-upload-ignore-tests").attr("disabled", "disabled");
                }
            });
        });

    //--></script>';
        }
    } else {
        $icon = '';
        if (Addon::exists($addonkey)) {
            $icon = '<a class="rex-link-expanded" href="' . Url::currentBackendPage(['addonkey' => $addonkey, 'file' => 'new']) . '" title="' . $package->i18n('file_add') . '"><i class="rex-icon rex-icon-add-package"></i></a>';
        }

        $panel = '

        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $package->i18n('name') . '</th>
                <td data-title="' . $package->i18n('name') . '">' . escape($addon['name']) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('author') . '</th>
                <td data-title="' . $package->i18n('author') . '">' . escape($addon['author']) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('shortdescription') . '</th>
                <td data-title="' . $package->i18n('shortdescription') . '">' . nl2br(escape($addon['shortdescription'])) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('description') . '</th>
                <td data-title="' . $package->i18n('description') . '">' . nl2br(escape($addon['description'])) . '</td>
            </tr>';

        if ($addon['website']) {
            $panel .= '
                <tr>
                    <th>' . $package->i18n('website') . '</th>
                    <td data-title="' . $package->i18n('website') . '"><a class="rex-link-expanded" href="' . escape($addon['website']) . '">' . escape($addon['website']) . '</a></td>
                </tr>';
        }

        $panel .= '
                </tbody>
            </table>';

        $fragment = new Fragment();
        $fragment->setVar('title', escape($addonkey) . ' <small>' . $package->i18n('information') . '</small>', false);
        $fragment->setVar('content', $panel, false);
        $content = $fragment->parse('core/page/section.php');

        echo $content;

        $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon">' . $icon . '</th>
                <th class="rex-table-width-4">' . $package->i18n('version') . '</th>
                <th>REDAXO</th>
                <th>' . $package->i18n('description') . '</th>
                <th class="rex-table-action" colspan="2">' . $package->i18n('status') . '</th>
            </tr>
            </thead>
            <tbody>';

        foreach ($addon['files'] as $fileId => $file) {
            $url = Url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId]);
            $status = $file['status'] ? 'online' : 'offline';
            $panel .= '
            <tr data-pjax-scroll-to="0">
                <td class="rex-table-icon"><a class="rex-link-expanded" href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $package->i18n('version') . '">' . escape($file['version']) . '</td>
                <td data-title="REDAXO">' . escape(implode(', ', $file['redaxo_versions'])) . '</td>
                <td data-title="' . $package->i18n('description') . '">' . $markdown($file['description']) . '</td>
                <td class="rex-table-action"><a class="rex-link-expanded" href="' . $url . '"><i class="rex-icon rex-icon-edit"></i> ' . $package->i18n('file_edit') . '</a></td>
                <td class="rex-table-action"><span class="rex-text-' . $status . '"><i class="rex-icon rex-icon-' . $status . '"></i> ' . $package->i18n($status) . '</span></td>
            </tr>';
        }

        $panel .= '</tbody></table>';

        $fragment = new Fragment();
        $fragment->setVar('title', $package->i18n('files'), false);
        $fragment->setVar('content', $panel, false);
        $content = $fragment->parse('core/page/section.php');

        echo $content;

        echo '<a class="btn btn-back" href="' . Url::currentBackendPage() . '">' . I18n::msg('back') . '</a>';
    }
} else {
    $panel = '
        <table class="table table-striped table-hover">
         <thead>
            <tr>
                <th class="rex-table-icon"><a class="rex-link-expanded" href="' . Url::currentBackendPage(['func' => 'reload']) . '" title="' . $package->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $package->i18n('key') . '</th>
                <th>' . $package->i18n('name') . '</th>
                <th class="rex-table-action" colspan="2">' . $package->i18n('status') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        $url = Url::currentBackendPage(['addonkey' => $key]);
        $status = $addon['status'] ? 'online' : 'offline';
        $panel .= '
            <tr data-pjax-scroll-to="0">
                <td class="rex-table-icon"><a class="rex-link-expanded" href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $package->i18n('key') . '">' . escape($key) . '</td>
                <td data-title="' . $package->i18n('name') . '">' . escape($addon['name']) . '</td>
                <td class="rex-table-action"><a class="rex-link-expanded" href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . I18n::msg('view') . '</a></td>
                <td class="rex-table-action"><span class="rex-text-' . $status . '">' . $package->i18n($status) . '</span></td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new Fragment();
    $fragment->setVar('title', $package->i18n('my_packages'), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
