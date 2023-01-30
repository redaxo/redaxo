<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($errors) && is_array($errors));
assert(isset($cancelSetupBtn));

$tablesComplete = '' == rex_setup_importer::verifyDbSchema();

$createdb = rex_post('createdb', 'int', '');

$supportsUtf8mb4 = rex_setup_importer::supportsUtf8mb4();
$existingUtf8mb4 = false;
$utf8mb4 = false;
if ($supportsUtf8mb4) {
    $utf8mb4 = rex_post('utf8mb4', 'bool', true);
    $existingUtf8mb4 = $utf8mb4;
    if ($tablesComplete) {
        $data = rex_sql::factory()->getArray('SELECT value FROM '.rex::getTable('config').' WHERE namespace="core" AND `key`="utf8mb4"');
        $existingUtf8mb4 = isset($data[0]['value']) ? json_decode((string) $data[0]['value']) : false;
    }
}

$headline = rex_view::title(rex_i18n::msg('setup_400').$cancelSetupBtn);

$content = '
            <fieldset class="rex-js-setup-step-4">
            ';

$submitMessage = rex_i18n::msg('setup_411');
if (count($errors) > 0) {
    $errors[] = rex_view::error(rex_i18n::msg('setup_403'));
    $headline .= implode('', $errors);
    $submitMessage = rex_i18n::msg('setup_412');
}

foreach (rex_setup::checkDbSecurity() as $message) {
    $headline .= rex_view::warning($message);
}

$dbchecked = array_fill(0, 6, '');
switch ($createdb) {
    case rex_setup::DB_MODE_SETUP_AND_OVERRIDE:
    case rex_setup::DB_MODE_SETUP_SKIP:
    case rex_setup::DB_MODE_SETUP_IMPORT_BACKUP:
    case rex_setup::DB_MODE_SETUP_UPDATE_FROM_PREVIOUS:
        $dbchecked[$createdb] = ' checked="checked"';
        break;
    default:
        if ($tablesComplete) {
            $dbchecked[rex_setup::DB_MODE_SETUP_SKIP] = ' checked="checked"';
        } else {
            $dbchecked[rex_setup::DB_MODE_SETUP_NO_OVERRIDE] = ' checked="checked"';
        }
}

// Vorhandene Exporte auslesen
$selExport = new rex_select();
$selExport->setName('import_name');
$selExport->setId('rex-form-import-name');
$selExport->setSize(1);
$selExport->setStyle('class="form-control selectpicker rex-js-import-name"');
$selExport->setAttribute('onclick', 'checkInput(\'createdb_3\')');
$exportDir = rex_backup::getDir();
$exportsFound = false;

if (is_dir($exportDir)) {
    $exportSqls = [];

    if ($handle = opendir($exportDir)) {
        while (false !== ($file = readdir($handle))) {
            if ('.' == $file || '..' == $file) {
                continue;
            }

            $isSql = ('.sql' == substr($file, strlen($file) - 4));
            if ($isSql) {
                // cut .sql
                $exportSqls[] = substr($file, 0, -4);
                $exportsFound = true;
            }
        }
        closedir($handle);
    }

    foreach ($exportSqls as $sqlExport) {
        $selExport->addOption($sqlExport, $sqlExport);
    }
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-createdb-0">' . rex_i18n::msg('setup_404') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-createdb-0" name="createdb" value="'. rex_setup::DB_MODE_SETUP_NO_OVERRIDE .'"' . $dbchecked[0] . ' />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-createdb-1">' . rex_i18n::msg('setup_405') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-createdb-1" name="createdb" value="'. rex_setup::DB_MODE_SETUP_AND_OVERRIDE .'"' . $dbchecked[1] . ' />';
$n['note'] = rex_i18n::msg('setup_405_note');
$formElements[] = $n;

if ($tablesComplete) {
    $n = [];
    $n['label'] = '<label for="rex-form-createdb-2">' . rex_i18n::msg('setup_406') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-2" name="createdb" value="'. rex_setup::DB_MODE_SETUP_SKIP .'"' . $dbchecked[2] . ' />';
    $n['note'] = rex_i18n::msg('setup_406_note');
    $formElements[] = $n;
}

$n = [];
$n['label'] = '<label for="rex-form-createdb-4">' . rex_i18n::msg('setup_414') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-createdb-4" name="createdb" value="'. rex_setup::DB_MODE_SETUP_UPDATE_FROM_PREVIOUS .'"' . $dbchecked[4] . ' />';
$n['note'] = rex_i18n::msg('setup_414_note');
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$mode = $fragment->parse('core/form/radio.php');

if ($exportsFound) {
    $formElements = [];
    $n = [];
    $n['label'] = '<label for="rex-form-createdb-3">' . rex_i18n::msg('setup_407') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-3" name="createdb" value="'. rex_setup::DB_MODE_SETUP_IMPORT_BACKUP .'"' . $dbchecked[3] . ' />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $mode .= $fragment->parse('core/form/radio.php');

    $formElements = [];
    $n = [];
    $n['field'] = $selExport->get();
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
$n['label'] = '<label class="required">'.rex_i18n::msg('mode').'</label>';
$n['field'] = $mode;
$formElements[] = $n;

$n = [];
$n['label'] = '<label class="required">'.rex_i18n::msg('charset').'</label>';
$n['field'] = $charset;
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . $submitMessage . '">' . $submitMessage . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '</form>';

$content .= '
            <script type="text/javascript" nonce="' . rex_response::getNonce() . '">
                 <!--
                jQuery(function($) {
                    var $container = $(".rex-js-setup-step-4");

                    // when opening backup dropdown -> mark corresponding radio button as checked
                    $container.find(".rex-js-import-name").click(function () {
                        $container.find("[name=createdb][value='. rex_setup::DB_MODE_SETUP_IMPORT_BACKUP .']").prop("checked", true);
                    });

                    if (!$container.find("[name=utf8mb4][value='. rex_setup::DB_MODE_SETUP_AND_OVERRIDE .']").prop("disabled")) {
                        var update = function () {
                            // when changing mode -> reset disabled state
                            $container.find("[name=utf8mb4]").prop("disabled", false);

                            switch (parseInt($container.find("[name=createdb]:checked").val())) {
                                case '. rex_setup::DB_MODE_SETUP_SKIP .':
                                    // when selecting "existing db" -> select current charset and disable radios
                                    $container.find("[name=utf8mb4][value='.((int) $existingUtf8mb4).']").prop("checked", true);
                                    $container.find("[name=utf8mb4]").prop("disabled", true);
                                    break;
                                case '. rex_setup::DB_MODE_SETUP_UPDATE_FROM_PREVIOUS .':
                                    // when selecting "update db" -> select utf8mb4 charset
                                    $container.find("[name=utf8mb4][value=1]").prop("checked", true);
                                    break;
                                case '. rex_setup::DB_MODE_SETUP_IMPORT_BACKUP .':
                                    // when selecting "import backup" -> disable radios
                                    $container.find("[name=utf8mb4]").prop("disabled", true);
                                    break;

                            }
                        }

                        $container.find("[name=createdb]").click(update);
                        update();
                    }
                });
                 //-->
            </script>';

echo $headline;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('setup_401'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form action="' . $context->getUrl(['step' => 5]) . '" method="post">' . $content . '</form>';
