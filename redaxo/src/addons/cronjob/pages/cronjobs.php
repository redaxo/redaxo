<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$func = rex_request('func', 'string');
$oid = rex_request('oid', 'int');

$csrfToken = rex_csrf_token::factory('cronjob');

if (in_array($func, ['setstatus', 'delete', 'execute']) && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    $func = '';
} elseif ($func == 'setstatus') {
    $manager = rex_cronjob_manager_sql::factory();
    $name = $manager->getName($oid);
    $status = (rex_request('oldstatus', 'int') + 1) % 2;
    $msg = $status == 1 ? 'status_activate' : 'status_deactivate';
    if ($manager->setStatus($oid, $status)) {
        echo rex_view::success($this->i18n($msg . '_success', $name));
    } else {
        echo rex_view::error($this->i18n($msg . '_error', $name));
    }
    $func = '';
} elseif ($func == 'delete') {
    $manager = rex_cronjob_manager_sql::factory();
    $name = $manager->getName($oid);
    if ($manager->delete($oid)) {
        echo rex_view::success($this->i18n('delete_success', $name));
    } else {
        echo rex_view::error($this->i18n('delete_error', $name));
    }
    $func = '';
} elseif ($func == 'execute') {
    $manager = rex_cronjob_manager_sql::factory();
    $name = $manager->getName($oid);
    $success = $manager->tryExecute($oid);
    $msg = '';
    if ($manager->hasMessage()) {
        $msg = '<br /><br />' . $this->i18n('log_message') . ': <br />' . nl2br($manager->getMessage());
    }
    if ($success) {
        echo rex_view::success($this->i18n('execute_success', $name) . $msg);
    } else {
        echo rex_view::error($this->i18n('execute_error', $name) . $msg);
    }
    $func = '';
}

if ($func == '') {
    $query = 'SELECT id, name, type, environment, execution_moment, nexttime, status FROM ' . REX_CRONJOB_TABLE . ' ORDER BY name';

    $list = rex_list::factory($query, 30, 'cronjobs');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $list->setNoRowsMessage($this->i18n('no_cronjobs'));

    $tdIcon = '<i class="rex-icon rex-icon-cronjob"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . $this->i18n('add') . '"><i class="rex-icon rex-icon-add-cronjob"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'oid' => '###id###']);

    $list->removeColumn('id');
    $list->removeColumn('type');

    $list->setColumnLabel('name', $this->i18n('name'));
    $list->setColumnParams('name', ['func' => 'edit', 'oid' => '###id###']);

    $list->setColumnLabel('environment', $this->i18n('environment'));
    $list->setColumnFormat('environment', 'custom', function ($params) {
        $value = $params['list']->getValue('environment');
        $env = [];
        if (strpos($value, '|frontend|') !== false) {
            $env[] = rex_i18n::msg('cronjob_environment_frontend');
        }
        if (strpos($value, '|backend|') !== false) {
            $env[] = rex_i18n::msg('cronjob_environment_backend');
        }
        if (strpos($value, '|script|') !== false) {
            $env[] = rex_i18n::msg('cronjob_environment_script');
        }
        return implode(', ', $env);
    });

    $list->setColumnLabel('execution_moment', $this->i18n('execution'));
    $list->setColumnFormat('execution_moment', 'custom', function ($params) {
        if ($params['list']->getValue('execution_moment')) {
            return rex_i18n::msg('cronjob_execution_beginning');
        }
        return rex_i18n::msg('cronjob_execution_ending');
    });

    $list->setColumnLabel('nexttime', $this->i18n('nexttime'));
    $list->setColumnFormat('nexttime', 'strftime', 'datetime');

    $list->setColumnLabel('status', $this->i18n('status_function'));
    $list->setColumnParams('status', ['func' => 'setstatus', 'oldstatus' => '###status###', 'oid' => '###id###'] + $csrfToken->getUrlParams());
    $list->setColumnLayout('status', ['<th class="rex-table-action" colspan="4">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnFormat('status', 'custom', function ($params) {
        $list = $params['list'];
        if (!class_exists($list->getValue('type'))) {
            $str = rex_i18n::msg('cronjob_status_invalid');
        } elseif ($list->getValue('status') == 1) {
            $str = $list->getColumnLink('status', '<span class="rex-online"><i class="rex-icon rex-icon-active-true"></i> ' . rex_i18n::msg('cronjob_status_activated') . '</span>');
        } else {
            $str = $list->getColumnLink('status', '<span class="rex-offline"><i class="rex-icon rex-icon-active-false"></i> ' . rex_i18n::msg('cronjob_status_deactivated') . '</span>');
        }
        return $str;
    });

    $list->addColumn('edit', '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('edit', ['func' => 'edit', 'oid' => '###id###']);

    $list->addColumn('delete', '<i class="rex-icon rex-icon-delete"></i> ' . $this->i18n('delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'oid' => '###id###'] + $csrfToken->getUrlParams());
    $list->addLinkAttribute('delete', 'data-confirm', $this->i18n('really_delete'));

    $list->addColumn('execute', '<i class="rex-icon rex-icon-execute"></i> ' . $this->i18n('execute'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('execute', ['func' => 'execute', 'oid' => '###id###'] + $csrfToken->getUrlParams());
    $list->addLinkAttribute('execute', 'data-pjax', 'false');
    $list->setColumnFormat('execute', 'custom', function ($params) {
        $list = $params['list'];
        if (strpos($list->getValue('environment'), '|backend|') !== false && class_exists($list->getValue('type'))) {
            return $list->getColumnLink('execute', '<i class="rex-icon rex-icon-execute"></i> ' . $this->i18n('execute'));
        }
        return '<span class="text-muted"><i class="rex-icon rex-icon-execute"></i> ' . $this->i18n('execute') . '</span>';
    });

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('caption'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
} elseif ($func == 'edit' || $func == 'add') {
    $fieldset = $func == 'edit' ? $this->i18n('edit') : $this->i18n('add');

    $form = new rex_cronjob_form(REX_CRONJOB_TABLE, $fieldset, 'id = ' . $oid, 'post', false);
    $form->addParam('oid', $oid);
    $form->setEditMode($func == 'edit');

    $form->addHiddenField('nexttime');

    $field = $form->addTextField('name');
    $field->setLabel($this->i18n('name'));
    $field->getValidator()->add('notEmpty', $this->i18n('cronjob_error_no_name'));
    $nameFieldId = $field->getAttribute('id');

    $field = $form->addTextAreaField('description');
    $field->setLabel($this->i18n('description'));

    $field = $form->addSelectField('environment');
    $field->setLabel($this->i18n('environment'));
    $field->setNotice($this->i18n('environment_notice'));
    $field->getValidator()->add('notEmpty', $this->i18n('cronjob_error_no_environment'));
    $field->setAttribute('multiple', 'multiple');
    $envFieldId = $field->getAttribute('id');
    $select = $field->getSelect();
    $select->setSize(3);
    $select->addOption($this->i18n('environment_frontend'), 'frontend');
    $select->addOption($this->i18n('environment_backend'), 'backend');
    $select->addOption($this->i18n('environment_script'), 'script');
    if ($func == 'add') {
        $select->setSelected([0, 1]);
    }

    $field = $form->addSelectField('execution_moment');
    $field->setLabel($this->i18n('execution'));
    $select = $field->getSelect();
    $select->setSize(1);
    $select->addOption($this->i18n('execution_beginning'), 1);
    $select->addOption($this->i18n('execution_ending'), 0);
    if ($func == 'add') {
        $select->setSelected(0);
    }

    $field = $form->addSelectField('status');
    $field->setLabel($this->i18n('status'));
    $select = $field->getSelect();
    $select->setSize(1);
    $select->addOption($this->i18n('status_activated'), 1);
    $select->addOption($this->i18n('status_deactivated'), 0);
    if ($func == 'add') {
        $select->setSelected(1);
    }

    $field = $form->addSelectField('type');
    $field->setLabel($this->i18n('type'));
    $select = $field->getSelect();
    $select->setSize(1);
    $typeFieldId = $field->getAttribute('id');
    $types = rex_cronjob_manager::getTypes();
    $cronjobs = [];
    foreach ($types as $class) {
        $cronjob = rex_cronjob::factory($class);
        if ($cronjob instanceof rex_cronjob) {
            $cronjobs[$class] = $cronjob;
            $select->addOption($cronjob->getTypeName(), $class);
        }
    }
    if ($func == 'add') {
        $select->setSelected('rex_cronjob_phpcode');
    }
    $activeType = $field->getValue();

    if ($func != 'add' && !in_array($activeType, $types)) {
        if (!$activeType && !$field->getValue()) {
            $warning = rex_i18n::rawMsg('cronjob_not_found');
        } else {
            $warning = rex_i18n::rawMsg('cronjob_type_not_found', $field->getValue(), $activeType);
        }
        rex_response::sendRedirect(rex_url::currentBackendPage([rex_request('list', 'string') . '_warning' => $warning], false));
    }

    $form->addFieldset($this->i18n('interval'));

    $field = $form->addIntervalField('interval');

    $form->addFieldset($this->i18n('type_parameters'));

    $fieldContainer = $form->addContainerField('parameters');
    $fieldContainer->setAttribute('style', 'display: none');
    $fieldContainer->setMultiple(false);
    $fieldContainer->setActive($activeType);

    $env_js = '';
    $visible = [];
    foreach ($cronjobs as $group => $cronjob) {
        $disabled = array_diff(['frontend', 'backend', 'script'], (array) $cronjob->getEnvironments());
        if (count($disabled) > 0) {
            $env_js .= '
                if ($("#' . $typeFieldId . ' option:selected").val() == "' . $group . '")
                    $("#' . $envFieldId . ' option[value=\'' . implode('\'], #' . $envFieldId . ' option[value=\'', $disabled) . '\']").prop("disabled","disabled").prop("selected","");
';
        }

        $params = $cronjob->getParamFields();

        if (!is_array($params) || empty($params)) {
            $field = $fieldContainer->addGroupedField($group, 'readonly', 'noparams', $this->i18n('type_no_parameters'));
            $field->setLabel('&nbsp;');
        } else {
            foreach ($params as $param) {
                $type = $param['type'];
                $name = $group . '_' . $param['name'];
                $label = !empty($param['label']) ? $param['label'] : '&nbsp;';
                $value = isset($param['default']) ? $param['default'] : null;
                $value = isset($param['value']) ? $param['value'] : $value;
                $attributes = isset($param['attributes']) ? $param['attributes'] : [];
                switch ($param['type']) {
                    case 'text':
                    case 'textarea':
                    case 'media':
                    case 'medialist':
                    case 'link':
                    case 'linklist':
                    case 'readonly':
                    case 'readonlytext':
                        $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
                        $field->setLabel($label);
                        if (isset($param['notice'])) {
                            $field->setNotice($param['notice']);
                        }
                        break;
                    case 'select':
                        $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
                        $field->setLabel($label);
                        $select = $field->getSelect();
                        $select->addArrayOptions($param['options']);
                        if (isset($param['notice'])) {
                            $field->setNotice($param['notice']);
                        }
                        break;
                    case 'checkbox':
                    case 'radio':
                        $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
                        $field->addArrayOptions($param['options']);
                        if (isset($param['notice'])) {
                            $field->setNotice($param['notice']);
                        }
                        break;
                    default:var_dump($param);
                }
                if (isset($param['visible_if']) && is_array($param['visible_if'])) {
                    foreach ($param['visible_if'] as $key => $value) {
                        $key = $group . '_' . $key;
                        if (!isset($visible[$key])) {
                            $visible[$key] = [];
                        }
                        if (!isset($visible[$key][$value])) {
                            $visible[$key][$value] = [];
                        }
                        $visible[$key][$value][] = $field->getAttribute('id');
                    }
                }
            }
        }
    }
    $visible_js = '';
    if (!empty($visible)) {
        foreach ($fieldContainer->getFields() as $group => $fieldElements) {
            foreach ($fieldElements as $field) {
                $name = $field->getFieldName();
                if (isset($visible[$name])) {
                    foreach ($visible[$name] as $value => $fieldIds) {
                        $visible_js .= '
                        var first = 1;
                        $("#' . $field->getAttribute('id') . '-' . $value . '").change(function(){
                            var checkbox = $(this);
                            $("#' . implode(',#', $fieldIds) . '").each(function(){
                                if ($(checkbox).is(":checked"))
                                    $(this).parent().parent().slideDown();
                                else if(first == 1)
                                    $(this).parent().parent().hide();
                                else
                                    $(this).parent().parent().slideUp();
                            });
                            first = 0;
                        }).change();';
                    }
                }
            }
        }
    }

    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $fieldset);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content; ?>

    <script type="text/javascript">
    // <![CDATA[
        jQuery(function($){
            var currentShown = null;
            $("#<?php echo $typeFieldId ?>").change(function(){
                if(currentShown) currentShown.hide();
                var typeId = "#rex-"+ $(this).val();
                currentShown = $(typeId);
                currentShown.show();
            }).change();
            $('#<?php echo $typeFieldId ?>').change(function(){
                $('#<?php echo $envFieldId ?> option').prop('disabled','');<?php echo $env_js; ?>
            }).change();<?php echo $visible_js . "\n"; ?>
        });
    // ]]>
    </script>

    <style>
        .rex-cronjob-interval-all .checkbox label {
            font-weight: 700;
        }

        .rex-cronjob-interval-hours .checkbox-inline,
        .rex-cronjob-interval-days .checkbox-inline,
        .rex-cronjob-interval-days .checkbox-inline {
            display: inline;
        }
        .rex-cronjob-interval-minutes .checkbox-inline,
        .rex-cronjob-interval-hours .checkbox-inline,
        .rex-cronjob-interval-days .checkbox-inline,
        .rex-cronjob-interval-weekdays .checkbox-inline,
        .rex-cronjob-interval-months .checkbox-inline {
            margin-left: 0;
            margin-right: 10px;
        }

        .rex-cronjob-interval-minutes .checkbox-inline label,
        .rex-cronjob-interval-hours .checkbox-inline label,
        .rex-cronjob-interval-days .checkbox-inline label,
        .rex-cronjob-interval-weekdays .checkbox-inline label,
        .rex-cronjob-interval-months .checkbox-inline label {
            margin-right: 10px;
            font-weight: 400;
        }
        .rex-cronjob-interval-minutes .checkbox-inline input[type="checkbox"],
        .rex-cronjob-interval-hours .checkbox-inline input[type="checkbox"],
        .rex-cronjob-interval-days .checkbox-inline input[type="checkbox"],
        .rex-cronjob-interval-weekdays .checkbox-inline input[type="checkbox"],
        .rex-cronjob-interval-months .checkbox-inline input[type="checkbox"] {
            top: 0;
        }
        .rex-cronjob-interval-hours .checkbox-inline:nth-child(12):after,
        .rex-cronjob-interval-days .checkbox-inline:nth-child(10):after,
        .rex-cronjob-interval-days .checkbox-inline:nth-child(20):after {
            content: '\A';
            white-space: pre;
        }
        .rex-cronjob-interval-hours .checkbox-inline:nth-child(13),
        .rex-cronjob-interval-days .checkbox-inline:nth-child(11),
        .rex-cronjob-interval-days .checkbox-inline:nth-child(21) {
            margin-left: 0;
        }
    </style>

<?php
}
