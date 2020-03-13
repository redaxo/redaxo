<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($errors) && is_array($errors));

$tables_complete = ('' == rex_setup_importer::verifyDbSchema()) ? true : false;

$createdb = rex_post('createdb', 'int', '');

$supportsUtf8mb4 = rex_setup_importer::supportsUtf8mb4();
$existingUtf8mb4 = false;
$utf8mb4 = false;
if ($supportsUtf8mb4) {
    $utf8mb4 = rex_post('utf8mb4', 'bool', true);
    $existingUtf8mb4 = $utf8mb4;
    if ($tables_complete) {
        $data = rex_sql::factory()->getArray('SELECT value FROM '.rex::getTable('config').' WHERE namespace="core" AND `key`="utf8mb4"');
        $existingUtf8mb4 = isset($data[0]['value']) ? json_decode($data[0]['value']) : false;
    }
}

$headline = rex_view::title(rex_i18n::msg('setup_500'));

$content = '
            <fieldset class="rex-js-setup-step-5">
            ';

$submit_message = rex_i18n::msg('setup_511');
if (count($errors) > 0) {
    $errors[] = rex_view::error(rex_i18n::msg('setup_503'));
    $headline .= implode('', $errors);
    $submit_message = rex_i18n::msg('setup_512');
}

foreach (rex_setup::checkDbSecurity() as $message) {
    $headline .= rex_view::warning($message);
}

$dbchecked = array_fill(0, 6, '');
switch ($createdb) {
    case 1:
    case 2:
    case 3:
    case 4:
        $dbchecked[$createdb] = ' checked="checked"';
        break;
    default:
        $dbchecked[0] = ' checked="checked"';
}

// Vorhandene Exporte auslesen
$sel_export = new rex_select();
$sel_export->setName('import_name');
$sel_export->setId('rex-form-import-name');
$sel_export->setSize(1);
$sel_export->setStyle('class="form-control selectpicker rex-js-import-name"');
$sel_export->setAttribute('onclick', 'checkInput(\'createdb_3\')');
$export_dir = rex_backup::getDir();
$exports_found = false;

if (is_dir($export_dir)) {
    $export_sqls = [];

    if ($handle = opendir($export_dir)) {
        while (false !== ($file = readdir($handle))) {
            if ('.' == $file || '..' == $file) {
                continue;
            }

            $isSql = ('.sql' == substr($file, strlen($file) - 4));
            if ($isSql) {
                // cut .sql
                $export_sqls[] = substr($file, 0, -4);
                $exports_found = true;
            }
        }
        closedir($handle);
    }

    foreach ($export_sqls as $sql_export) {
        $sel_export->addOption($sql_export, $sql_export);
    }
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-createdb-0">' . rex_i18n::msg('setup_504') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-createdb-0" name="createdb" value="0"' . $dbchecked[0] . ' />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-createdb-1">' . rex_i18n::msg('setup_505') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-createdb-1" name="createdb" value="1"' . $dbchecked[1] . ' />';
$n['note'] = rex_i18n::msg('setup_505_note');
$formElements[] = $n;

if ($tables_complete) {
    $n = [];
    $n['label'] = '<label for="rex-form-createdb-2">' . rex_i18n::msg('setup_506') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-2" name="createdb" value="2"' . $dbchecked[2] . ' />';
    $n['note'] = rex_i18n::msg('setup_506_note');
    $formElements[] = $n;
}

$n = [];
$n['label'] = '<label for="rex-form-createdb-4">' . rex_i18n::msg('setup_514') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-createdb-4" name="createdb" value="4"' . $dbchecked[4] . ' />';
$n['note'] = rex_i18n::msg('setup_514_note');
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$mode = $fragment->parse('core/form/radio.php');

if ($exports_found) {
    $formElements = [];
    $n = [];
    $n['label'] = '<label for="rex-form-createdb-3">' . rex_i18n::msg('setup_507') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-3" name="createdb" value="3"' . $dbchecked[3] . ' />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $mode .= $fragment->parse('core/form/radio.php');

    $formElements = [];
    $n = [];
    $n['field'] = $sel_export->get();
    $n['note'] = rex_i18n::msg('backup_version_warning');
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $mode .= $fragment->parse('core/form/form.php');
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-utf8mb4">'.rex_i18n::msg('setup_charset_utf8mb4').'</label>';
$n['field'] = '<input type="radio" id="rex-form-utf8mb4" name="utf8mb4" value="1"'.($utf8mb4 ? ' checked' : '').($supportsUtf8mb4 ? '' : ' disabled').' />';
$n['note'] = rex_i18n::msg('setup_charset_utf8mb4_note');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-utf8">'.rex_i18n::msg('setup_charset_utf8').'</label>';
$n['field'] = '<input type="radio" id="rex-form-utf8" name="utf8mb4" value="0"'.($utf8mb4 ? '' : ' checked').' />';
$n['note'] = rex_i18n::msg('setup_charset_utf8_note');
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$charset = $fragment->parse('core/form/radio.php');

$formElements = [];

$sql = rex_sql::factory();

$n = [];
$n['label'] = '<label>'.rex_i18n::msg('version').'</label>';
$n['field'] = '<p class="form-control-static">'.$sql->getDbType().' '.$sql->getDbVersion().'</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label>'.rex_i18n::msg('mode').'</label>';
$n['field'] = $mode;
$formElements[] = $n;

$n = [];
$n['label'] = '<label>'.rex_i18n::msg('charset').'</label>';
$n['field'] = $charset;
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . $submit_message . '">' . $submit_message . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '</form>';

$content .= '
            <script type="text/javascript">
                 <!--
                jQuery(function($) {
                    var $container = $(".rex-js-setup-step-5");

                    // when opening backup dropdown -> mark corresponding radio button as checked
                    $container.find(".rex-js-import-name").click(function () {
                        $container.find("[name=createdb][value=3]").prop("checked", true);
                    });

                    if (!$container.find("[name=utf8mb4][value=1]").prop("disabled")) {
                        // when changing mode -> reset disabled state
                        $container.find("[name=createdb]").click(function () {
                            $container.find("[name=utf8mb4]").prop("disabled", false);
                        });

                        // when selecting "existing db" -> select current charset and disable radios
                        $container.find("[name=createdb][value=2]").click(function () {
                            $container.find("[name=utf8mb4][value='.((int) $existingUtf8mb4).']").prop("checked", true);
                            $container.find("[name=utf8mb4]").prop("disabled", true);
                        });

                        // when selecting "update db" -> select utf8mb4 charset
                        $container.find("[name=createdb][value=4]").click(function () {
                            $container.find("[name=utf8mb4][value=1]").prop("checked", true);
                        });

                        // when selecting "import backup" -> disable radios
                        $container.find("[name=createdb][value=3]").click(function () {
                            $container.find("[name=utf8mb4]").prop("disabled", true);
                        });
                    }
                });
                 //-->
            </script>';

echo $headline;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('setup_501'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form action="' . $context->getUrl(['step' => 6]) . '" method="post">' . $content . '</form>';
