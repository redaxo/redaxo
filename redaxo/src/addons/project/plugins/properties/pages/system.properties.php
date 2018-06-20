<?php
$content = '';
$buttons = '';

$func = rex_request('func', 'string');

$csrfToken = rex_csrf_token::factory('project_properties');

// Konfiguration speichern
if ($func == 'update' && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ($func == 'update') {

    $this->setConfig(rex_post('settings', [
        ['project_settings', 'string']
    ]));

    echo rex_view::success($this->i18n('config_saved'));
}

// Config-Werte bereitstellen
$Values = array();
$Values['project_settings'] = $this->getConfig('project_settings');

$content .= '<fieldset><legend>' . $this->i18n('config_title_legend') . '</legend>';

// project_settings
$formElements = [];
$n = [];

$file = rex_file::get(rex_path::plugin('project', 'properties') .'README.md');
$parser = rex_markdown::factory();
$hilfetext = $parser->parse($file);

$html = '
<div class="panel panel-default">
    <header class="panel-heading collapsed" data-toggle="collapse" data-target="#collapse-projectinfo">
        <div class="panel-title"><i class="rex-icon rex-icon-info"></i> ' . $this->i18n('project_config_title_help') . '</div>
    </header>
<div id="collapse-projectinfo" class="panel-collapse collapse">
    <div class="panel-body" style="background-color:#fff;">' . $hilfetext . '</div>
</div>
';

$n['field'] = $html;

$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

$formElements = [];
$n = [];

$n['label'] = '<label for="project_settings">' . htmlspecialchars_decode($this->i18n('config_project_settings')) . '</label>';
$n['field'] = '<textarea class="form-control rex-code" rows="25" id="project_settings" name="settings[project_settings]">' . $Values['project_settings'] . '</textarea>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

$content .= '</fieldset>';

// Save-Button
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $this->i18n('save') . '">' . $this->i18n('save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

// Ausgabe Section
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('title_config'), false);
$fragment->setVar('class', 'edit', false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="update" />
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>
';

echo $content;
