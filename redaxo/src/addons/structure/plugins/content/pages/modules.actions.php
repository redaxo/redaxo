<?php

/**
 *
 * @package redaxo5
 */

$PREPOST[0] = 'PRE';
$PREPOST[1] = 'POST';
$ASTATUS[0] = 'ADD';
$ASTATUS[1] = 'EDIT';
$ASTATUS[2] = 'DELETE';

class rex_event_select extends rex_select
{
    public function __construct($options)
    {
        parent::__construct();

        $this->setMultiple(1);

        foreach ($options as $key => $value) {
            $this->addOption($value, $key);
        }

        $this->setSize(count($options));
    }
}

$OUT = true;

$action_id = rex_request('action_id', 'int');
$function  = rex_request('function', 'string');
$save      = rex_request('save', 'int');
$goon      = rex_request('goon', 'string');

$success = '';
$error   = '';

$content = '';
$message = '';

if ($function == 'delete') {
    $del = rex_sql::factory();
//  $del->setDebug();
    $qry = 'SELECT
                        *
                    FROM
                        ' . rex::getTablePrefix() . 'action a,
                        ' . rex::getTablePrefix() . 'module_action ma
                    LEFT JOIN
                     ' . rex::getTablePrefix() . 'module m
                    ON
                        ma.module_id = m.id
                    WHERE
                        ma.action_id = a.id AND
                        ma.action_id=' . $action_id;
    $del->setQuery($qry); // module mit dieser aktion vorhanden ?
    if ($del->getRows() > 0) {
        $action_in_use_msg = '';
        $action_name = htmlspecialchars($del->getValue('a.name'));
        for ($i = 0; $i < $del->getRows(); $i++) {
            $action_in_use_msg .= '<li><a href="' . rex_url::backendPage('modules', ['function' => 'edit', 'module_id' => $del->getValue('ma.module_id')]) . '">' . htmlspecialchars($del->getValue('m.name')) . ' [' . $del->getValue('ma.module_id') . ']</a></li>';
            $del->next();
        }

        if ($action_in_use_msg != '') {
            $action_in_use_msg = '<ul>' . $action_in_use_msg . '</ul>';
        }

        $error = rex_i18n::msg('action_cannot_be_deleted', $action_name) . $action_in_use_msg;
    } else {
        $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . "action WHERE id='$action_id' LIMIT 1");
        $success = rex_i18n::msg('action_deleted');
    }
}

if ($function == 'add' || $function == 'edit') {
    $name           = rex_post('name', 'string');
    $previewaction  = rex_post('previewaction', 'string');
    $presaveaction  = rex_post('presaveaction', 'string');
    $postsaveaction = rex_post('postsaveaction', 'string');

    $previewstatus  = 255;
    $presavestatus  = 255;
    $postsavestatus = 255;

    if ($save == '1') {
        $faction = rex_sql::factory();

        $previewstatus  = rex_post('previewstatus', 'array');
        $presavestatus  = rex_post('presavestatus', 'array');
        $postsavestatus = rex_post('postsavestatus', 'array');

        $previewmode = 0;
        foreach ($previewstatus as $status) {
            $previewmode |= $status;
        }

        $presavemode = 0;
        foreach ($presavestatus as $status) {
            $presavemode |= $status;
        }

        $postsavemode = 0;
        foreach ($postsavestatus as $status) {
            $postsavemode |= $status;
        }

        $faction->setTable(rex::getTablePrefix() . 'action');
        $faction->setValue('name', $name);
        $faction->setValue('preview', $previewaction);
        $faction->setValue('presave', $presaveaction);
        $faction->setValue('postsave', $postsaveaction);
        $faction->setValue('previewmode', $previewmode);
        $faction->setValue('presavemode', $presavemode);
        $faction->setValue('postsavemode', $postsavemode);

        try {
            if ($function == 'add') {
                $faction->addGlobalCreateFields();

                $faction->insert();
                $success = rex_i18n::msg('action_added');
            } else {
                $faction->addGlobalUpdateFields();
                $faction->setWhere(['id' => $action_id]);

                $faction->update();
                $success = rex_i18n::msg('action_updated');
            }
        } catch (rex_sql_exception $e) {
            $error = $e->getMessage();
        }

        if (isset ($goon) and $goon != '') {
            $save = 'nein';
        } else {
            $function = '';
        }
    }

    if ($save != '1') {
        if ($function == 'edit') {
            $legend = rex_i18n::msg('action_edit') . ' <em>[' . rex_i18n::msg('id') . '=' . $action_id . ']</em>';

            $action = rex_sql::factory();
            $action->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'action WHERE id=' . $action_id);

            $name           = $action->getValue('name');
            $previewaction  = $action->getValue('preview');
            $presaveaction  = $action->getValue('presave');
            $postsaveaction = $action->getValue('postsave');
            $previewstatus  = $action->getValue('previewmode');
            $presavestatus  = $action->getValue('presavemode');
            $postsavestatus = $action->getValue('postsavemode');
        } else {
            $legend = rex_i18n::msg('action_create');
        }

        // PreView action macht nur bei add und edit Sinn da,
        // - beim Delete kommt keine View
        $options = [
            1 => $ASTATUS[0] . ' - ' . rex_i18n::msg('action_event_add'),
            2 => $ASTATUS[1] . ' - ' . rex_i18n::msg('action_event_edit')
        ];

        $sel_preview_status = new rex_event_select($options, false);
        $sel_preview_status->setName('previewstatus[]');
        $sel_preview_status->setId('previewstatus');

        $options = [
            1 => $ASTATUS[0] . ' - ' . rex_i18n::msg('action_event_add'),
            2 => $ASTATUS[1] . ' - ' . rex_i18n::msg('action_event_edit'),
            4 => $ASTATUS[2] . ' - ' . rex_i18n::msg('action_event_delete')
        ];

        $sel_presave_status = new rex_event_select($options);
        $sel_presave_status->setName('presavestatus[]');
        $sel_presave_status->setId('presavestatus');

        $sel_postsave_status = new rex_event_select($options);
        $sel_postsave_status->setName('postsavestatus[]');
        $sel_postsave_status->setId('postsavestatus');

        $allPreviewChecked = $previewstatus == 3 ? ' checked="checked"' : '';
        foreach ([1, 2, 4] as $var) {
            if (($previewstatus & $var) == $var) {
                $sel_preview_status->setSelected($var);
            }
        }

        $allPresaveChecked = $presavestatus == 7 ? ' checked="checked"' : '';
        foreach ([1, 2, 4] as $var) {
            if (($presavestatus & $var) == $var) {
                $sel_presave_status->setSelected($var);
            }
        }

        $allPostsaveChecked = $postsavestatus == 7 ? ' checked="checked"' : '';
        foreach ([1, 2, 4] as $var) {
            if (($postsavestatus & $var) == $var) {
                $sel_postsave_status->setSelected($var);
            }
        }

        $btn_update = '';
        if ($function != 'add') {
            $btn_update = '<button class="rex-button btn btn-primary" type="submit" name="goon"' . rex::getAccesskey(rex_i18n::msg('save_action_and_continue'), 'apply') . '>' . rex_i18n::msg('save_action_and_continue') . '</button>';
        }

        if ($success != '') {
            $message .= rex_view::success($success);
        }

        if ($error != '') {
            $message .= rex_view::error($error);
        }

        $content .= '
            <div class="rex-form" id="rex-form-action">
                <form action="' . rex_url::currentBackendPage() . '" method="post">
                    <fieldset>
                        <h2>' . $legend . ' </h2>

                            <input type="hidden" name="function" value="' . $function . '" />
                            <input type="hidden" name="save" value="1" />
                            <input type="hidden" name="action_id" value="' . $action_id . '" />';


        $formElements = [];

        $n = [];
        $n['label'] = '<label for="name">' . rex_i18n::msg('action_name') . '</label>';
        $n['field'] = '<input type="text" id="name" name="name" value="' . htmlspecialchars($name) . '" />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        $content .= '
                    </fieldset>

                    <fieldset>
                        <h2>Preview-Action <em>[' . rex_i18n::msg('action_mode_preview') . ']</em></h2>';


        $formElements = [];
        $n = [];
        $n['label'] = '<label for="previewaction">' . rex_i18n::msg('input') . '</label>';
        $n['field'] = '<textarea class="rex-code" name="previewaction" id="previewaction">' . htmlspecialchars($previewaction) . '</textarea>';
        $n['note']  = rex_i18n::msg('action_hint');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');


        $formElements = [];
        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label>' . rex_i18n::msg('action_event_all') . '</label>';
        $n['field'] = '<input id="rex-js-preview-allevents" type="checkbox" name="preview_allevents" ' . $allPreviewChecked . ' />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/checkbox.php');


        $formElements = [];
        $n = [];
        $n['id'] = 'rex-js-preview-events';
        $n['label'] = '<label for="previestatus">' . rex_i18n::msg('action_event') . '</label>';
        $n['field'] = $sel_preview_status->get();
        $n['note']  = rex_i18n::msg('ctrl');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        $content .= '
                    </fieldset>

                    <fieldset>
                        <h2>Presave-Action <em>[' . rex_i18n::msg('action_mode_presave') . ']</em></h2>';


        $formElements = [];
        $n = [];
        $n['label'] = '<label for="presaveaction">' . rex_i18n::msg('input') . '</label>';
        $n['field'] = '<textarea class="rex-code" name="presaveaction" id="presaveaction">' . htmlspecialchars($presaveaction) . '</textarea>';
        $n['note'] = rex_i18n::msg('action_hint');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');


        $formElements = [];
        $n = [];
        $n['label'] = '<label>' . rex_i18n::msg('action_event_all') . '</label>';
        $n['field'] = '<input id="rex-js-presave-allevents" type="checkbox" name="presave_allevents" ' . $allPresaveChecked . ' />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/checkbox.php');


        $formElements = [];
        $n = [];
        $n['id'] = 'rex-js-presave-events';
        $n['label'] = '<label for="presavestatus">' . rex_i18n::msg('action_event') . '</label>';
        $n['field'] = $sel_presave_status->get();
        $n['note'] = rex_i18n::msg('ctrl');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        $content .= '
                    </fieldset>


                    <fieldset>
                        <h2>Postsave-Action <em>[' . rex_i18n::msg('action_mode_postsave') . ']</em></h2>';


        $formElements = [];
        $n = [];
        $n['label'] = '<label for="postsaveaction">' . rex_i18n::msg('input') . '</label>';
        $n['field'] = '<textarea class="rex-code" name="postsaveaction" id="postsaveaction">' . htmlspecialchars($postsaveaction) . '</textarea>';
        $n['note']  = rex_i18n::msg('action_hint');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');


        $formElements = [];
        $n = [];
        $n['label'] = '<label>' . rex_i18n::msg('action_event_all') . '</label>';
        $n['field'] = '<input id="rex-js-postsave-allevents" type="checkbox" name="postsave_allevents" ' . $allPostsaveChecked . ' />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/checkbox.php');


        $formElements = [];
        $n = [];
        $n['id'] = 'rex-js-postsave-events';
        $n['label'] = '<label for="postsavestatus">' . rex_i18n::msg('action_event') . '</label>';
        $n['field'] = $sel_postsave_status->get();
        $n['note']  = rex_i18n::msg('ctrl');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        $content .= '</fieldset>';


        $formElements = [];

        $fragment = new rex_fragment();

        $n = [];
        $n['field'] = '<a class="rex-back" href="' . rex_url::currentBackendPage() . '"><span class="rex-icon rex-icon-back"></span>' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="rex-button btn btn-primary" type="submit"' . rex::getAccesskey(rex_i18n::msg('save_action_and_quit'), 'save') . '>' . rex_i18n::msg('save_action_and_quit') . '</button>';
        $formElements[] = $n;

        if ($btn_update != '') {
            $n = [];
            $n['field'] = $btn_update;
            $formElements[] = $n;
        }

        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/submit.php');

        $content .= '
                </form>
            </div>

            <script type="text/javascript">
            <!--

            jQuery(function($) {
                var eventTypes = "#rex-js-preview #rex-js-presave #rex-js-postsave";

                $(eventTypes.split(" ")).each(function() {
                    var eventType = this;
                    $(eventType+ "-allevents").click(function() {
                        $(eventType+"-events").slideToggle("slow");
                    });

                    if($(eventType+"-allevents").is(":checked")) {
                        $(eventType+"-events").hide();
                    }
                });
            });

            -->
            </script>
            ';

        $OUT = false;
    }
}

if ($OUT) {
    if ($success != '') {
        $message .= rex_view::success($success);
    }

    if ($error != '') {
        $message .= rex_view::error($error);
    }

    // ausgabe actionsliste !
    $content .= '
        <table class="rex-table table table-striped" id="rex-table-action">
            <caption>' . rex_i18n::msg('action_caption') . '</caption>
            <thead>
                <tr>
                    <th class="rex-slim"><a href="' . rex_url::currentBackendPage(['function' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('action_create'), 'add') . ' title="' . rex_i18n::msg('action_create') . '"><span class="rex-icon rex-icon-add-action"></span></a></th>
                    <th class="rex-slim">' . rex_i18n::msg('id') . '</th>
                    <th class="name">' . rex_i18n::msg('action_name') . '</th>
                    <th class="preview">Preview-Event(s)</th>
                    <th class="presave">Presave-Event(s)</th>
                    <th class="postsave">Postsave-Event(s)</th>
                    <th class="function">' . rex_i18n::msg('action_functions') . '</th>
                </tr>
            </thead>
        ';

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'action ORDER BY name');
    $rows = $sql->getRows();

    if ($rows > 0) {
        $content .= '<tbody>' . "\n";

        for ($i = 0; $i < $rows; $i++) {
            $previewmode = [];
            $presavemode = [];
            $postsavemode = [];

            foreach ([1 => 'ADD', 2 => 'EDIT', 4 => 'DELETE'] as $var => $value) {
                if (($sql->getValue('previewmode') & $var) == $var) {
                    $previewmode[] = $value;
                }
            }

            foreach ([1 => 'ADD', 2 => 'EDIT', 4 => 'DELETE'] as $var => $value) {
                if (($sql->getValue('presavemode') & $var) == $var) {
                    $presavemode[] = $value;
                }
            }

            foreach ([1 => 'ADD', 2 => 'EDIT', 4 => 'DELETE'] as $var => $value) {
                if (($sql->getValue('postsavemode') & $var) == $var) {
                    $postsavemode[] = $value;
                }
            }

            $content .= '
                        <tr>
                            <td class="rex-slim"><a href="' . rex_url::currentBackendPage(['action_id' => $sql->getValue('id'), 'function' => 'edit']) . '" title="' . htmlspecialchars($sql->getValue('name')) . '"><span class="rex-icon rex-icon-action"></span></a></td>
                            <td class="rex-slim">' . $sql->getValue('id') . '</td>
                            <td class="name"><a href="' . rex_url::currentBackendPage(['action_id' => $sql->getValue('id'), 'function' => 'edit']) . '">' . htmlspecialchars($sql->getValue('name')) . '</a></td>
                            <td class="preview">' . implode('/', $previewmode) . '</td>
                            <td class="presave">' . implode('/', $presavemode) . '</td>
                            <td class="postsave">' . implode('/', $postsavemode) . '</td>
                            <td class="delete"><a class="rex-delete" href="' . rex_url::currentBackendPage(['action_id' => $sql->getValue('id'), 'function' => 'delete']) . '" data-confirm="' . rex_i18n::msg('action_delete') . ' ?">' . rex_i18n::msg('action_delete') . '</a></td>
                        </tr>
                    ';

            $sql->next();
        }

        $content .= '</tbody>' . "\n";
    }

    $content .= '
        </table>';

    if ($rows < 1) {
        $content .= '<div class="rex-content-information"><p>' . rex_i18n::msg('actions_not_found') . '</p></div>';
    }
}

echo $message;
echo rex_view::content('block', $content, '', $params = ['flush' => true]);
