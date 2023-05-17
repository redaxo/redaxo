<?php

use Redaxo\Core\Fragment\Component\Alert\Error;
use Redaxo\Core\Fragment\Component\Alert\Info;
use Redaxo\Core\Fragment\Component\Alert\Success;
use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonSize;
use Redaxo\Core\Fragment\Component\ButtonType;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Card;
use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Component\Input;
use Redaxo\Core\Fragment\Component\InputType;
use Redaxo\Core\Fragment\Html;
use Redaxo\Core\Fragment\HtmlAttributes;
use Redaxo\Core\Fragment\Page\SystemSettings;

/**
 * @var SystemSettings $this
 * @psalm-scope-this SystemSettings
 */
?>

<?php if ($this->errors): ?>
    <?= (new Error(
        body: new Html(implode('<br>', $this->errors)),
    ))->render() ?>
<?php endif ?>

<?php if ($this->success): ?>
    <?= (new Success(
        body: new Html($this->success),
    ))->render() ?>
<?php endif ?>

<div class="row">
    <div class="col-lg-8">
        <form id="rex-form-system-setup" action="<?= rex_url::currentBackendPage() ?>" method="post">
            <input type="hidden" name="func" value="updateinfos" />
            <?= $this->csrfToken->getHiddenField() ?>

            <?= (new Card(
                header: rex_i18n::msg('system_settings'),

                body: new Html(function () { ?>
                    <?= (new Input(
                        label: rex_i18n::msg('server'),
                        type: InputType::Url,
                        name: 'settings[server]',
                        value: rex::getServer(),
                        required: true,
                    ))->render() ?>

                    <?= (new Input(
                        label: rex_i18n::msg('servername'),
                        name: 'settings[servername]',
                        value: rex::getServerName(),
                        required: true,
                    ))->render() ?>

                    <?= (new Choice(
                        label: rex_i18n::msg('backend_language'),
                        name: 'settings[lang]',
                        value: rex::getProperty('lang'),
                        choices: $this->getLangChoices(),
                        required: true,
                    ))->render() ?>

                    <?= (new Input(
                        label: rex_i18n::msg('error_email'),
                        name: 'settings[error_email]',
                        value: rex::getErrorEmail(),
                        required: true,
                    ))->render() ?>

                    <?php foreach ($this->getSystemSettings() as $setting): ?>
                        <?= $setting->get() ?>
                    <?php endforeach ?>

                    <?php if ($url = $this->editor->getUrl($this->configYml, 0)): ?>
                        <?= (new Button(
                            label: rex_i18n::rawMsg('system_editor_open_file', rex_path::basename($this->configYml)),
                            href: $url,
                            variant: ButtonVariant::Primary,
                            size: ButtonSize::Small,
                        ))->render() ?>

                        <p>
                            <?= rex_i18n::msg('system_edit_config_note') ?>
                        </p>
                    <?php endif ?>
                <?php }),

                footer: new Html(static function () { ?>
                    <?= (new Button\Save(
                        name: 'sendit',
                    ))->render() ?>
                <?php }),
            ))->render() ?>
        </form>

        <form id="rex-form-system-setup" action="<?= rex_url::currentBackendPage() ?>" method="post">
            <input type="hidden" name="func" value="update_editor" />
            <?= $this->csrfToken->getHiddenField() ?>
            <?= (new Card(
                header: rex_i18n::msg('system_editor'),

                body: new Html(function () { ?>
                    <p><?= rex_i18n::msg('system_editor_note') ?></p>

                    <?= (new Choice(
                        label: rex_i18n::msg('system_editor_name'),
                        name: 'editor[name]',
                        value: $this->editor->getName(),
                        choices: [rex_i18n::msg('system_editor_no_editor') => ''] + array_flip($this->editor->getSupportedEditors()),
                    ))->render() ?>

                    <?= (new Input(
                        label: rex_i18n::msg('system_editor_basepath'),
                        name: 'editor[basepath]',
                        value: rex_escape($this->editor->getBasepath()),
                        notice: rex_i18n::msg('system_editor_basepath_note'),
                    ))->render() ?>

                    <?= $this->editorViaCookie ? (new Info(
                        body: rex_i18n::msg('system_editor_note_cookie'),
                    ))->render() : '' ?>
                <?php }),

                footer: new Html(function () { ?>
                    <div>
                        <?= (new Button\Save(
                            label: rex_i18n::rawMsg('system_editor_update_cookie'),
                            name: 'editor[update_cookie]',
                            value: '1',
                        ))->render() ?>

                        <?php if ($this->editorViaCookie): ?>
                            <?= (new Button(
                                label: rex_i18n::rawMsg('system_editor_delete_cookie'),
                                variant: ButtonVariant::Danger,
                                type: ButtonType::Submit,
                                name: 'editor[delete_cookie]',
                                value: '1',
                            ))->render() ?>
                        <?php else: ?>
                            <?= (new Button\Save(
                                label: rex_i18n::rawMsg('system_editor_update_configyml'),
                                name: 'editor[update_cookie]',
                                value: '0',
                            ))->render() ?>
                        <?php endif ?>
                    </div>
                <?php }),
            ))->render() ?>
        </form>
    </div>
    <div class="col-lg-4">
        <?= (new Card(
            header: rex_i18n::msg('system_features'),

            body: new Html(function () { ?>
                <h3><?= rex_i18n::msg('delete_cache') ?></h3>
                <p><?= rex_i18n::msg('delete_cache_description') ?></p>
                <p>
                    <?= (new Button(
                        label: rex_i18n::rawMsg('delete_cache'),
                        href: rex_url::currentBackendPage(['func' => 'generate'] + $this->csrfToken->getUrlParams()),
                        variant: ButtonVariant::Danger,
                    ))->render() ?>
                </p>

                <h3><?= rex_i18n::msg('debug_mode') ?></h3>
                <p><?= rex_i18n::msg('debug_mode_note') ?></p>
                <p>
                    <?= (new Button(
                        label: rex_i18n::rawMsg('debug_mode_'.(rex::isDebugMode() ? 'off' : 'on')),
                        prefix: new Icon(IconLibrary::Debug),
                        href: (rex_url::currentBackendPage(['func' => 'debugmode'] + $this->csrfToken->getUrlParams())),
                        variant: ButtonVariant::Warning,
                        attributes: new HtmlAttributes([
                            'data-pjax' => 'false',
                            'data-confirm' => rex::isDebugMode() ? null : rex_i18n::msg('debug_confirm'),
                        ]),
                    ))->render() ?>
                </p>

                <h3><?= rex_i18n::msg('safemode') ?></h3>
                <p><?= rex_i18n::msg('safemode_text') ?></p>
                <p>
                    <?= (new Button(
                        label: rex_i18n::rawMsg('safemode_'.(rex::isSafeMode() ? 'deactivate' : 'activate')),
                        href: rex_url::currentBackendPage(['safemode' => (rex::isSafeMode() ? '0' : '1')] + $this->csrfToken->getUrlParams()),
                        variant: ButtonVariant::Warning,
                        attributes: new HtmlAttributes([
                            'data-pjax' => 'false',
                            'class' => 'rex-toggle-safemode',
                        ]),
                    ))->render() ?>
                </p>

                <h3><?= rex_i18n::msg('setup') ?></h3>
                <p><?= rex_i18n::msg('setup_text') ?></p>
                <p>
                    <?= (new Button(
                        label: rex_i18n::rawMsg('setup'),
                        href: rex_url::currentBackendPage(['func' => 'setup'] + $this->csrfToken->getUrlParams()),
                        variant: ButtonVariant::Primary,
                        attributes: new HtmlAttributes([
                            'data-pjax' => 'false',
                            'data-confirm' => rex_i18n::msg('setup_restart'),
                        ]),
                    ))->render() ?>
                </p>
            <?php }),
        ))->render() ?>

        <?= (new Card(
            header: rex_i18n::msg('installation'),

            body: new Html(function () { ?>
                <table class="table">
                    <tr>
                        <th class="rex-table-width-3">REDAXO</th>
                        <td>
                            <?php if (rex_version::isUnstable($this->rexVersion)): ?>
                                <?= (new Icon(
                                    name: IconLibrary::VersionUnstable,
                                    label: rex_i18n::msg('unstable_version'),
                                ))->render() ?>
                            <?php endif ?>
                            <?= rex_escape($this->rexVersion) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>PHP</th>
                        <td>
                            <?= (new Button(
                                label: PHP_VERSION,
                                suffix: new Icon(IconLibrary::PhpInfo),
                                href: rex_url::backendPage('system/phpinfo'),
                                attributes: new HtmlAttributes([
                                    'onclick' => 'newWindow("phpinfo", this.href, 1000,800,",status=yes,resizable=yes"); return false;',
                                ]),
                            ))->render();
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= rex_i18n::msg('path') ?></th>
                        <td>
                            <div class="rex-word-break"><?= rex_path::base() ?></div>
                        </td>
                    </tr>
                </table>
            <?php }),
        ))->render() ?>

        <?= (new Card(
            header: rex_i18n::msg('database'),

            body: new Html(function () { ?>
                <table class="table">
                    <tr>
                        <th class="rex-table-width-3"><?= rex_i18n::msg('version') ?></th>
                        <td><?= $this->sql->getDbType() ?> <?= rex_escape($this->sql->getDbVersion()) ?></td>
                    </tr>
                    <tr>
                        <th><?= rex_i18n::msg('name') ?></th>
                        <td><span class="rex-word-break"><?= rex_escape($this->dbConfig->name) ?></span></td>
                    </tr>
                    <tr>
                        <th><?= rex_i18n::msg('host') ?></th>
                        <td><?= rex_escape($this->dbConfig->host) ?></td>
                    </tr>
                </table>
            <?php }),
        ))->render() ?>
    </div>
</div>
