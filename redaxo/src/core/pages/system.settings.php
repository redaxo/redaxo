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
use Redaxo\Core\Fragment\Slot;

$alertError = [];
$alertSuccess = '';

$func = rex_request('func', 'string');
$csrfToken = rex_csrf_token::factory('system');

if (rex_request('rex_debug_updated', 'bool', false)) {
    $alertSuccess = (rex::isDebugMode()) ? rex_i18n::msg('debug_mode_info_on') : rex_i18n::msg('debug_mode_info_off');
}

if ($func && !$csrfToken->isValid()) {
    $alertError[] = rex_i18n::msg('csrf_token_invalid');
} elseif ('setup' == $func) {
    // REACTIVATE SETUP
    if (false !== $url = rex_setup::startWithToken()) {
        header('Location:' . $url);
        exit;
    }
    $alertError[] = rex_i18n::msg('setup_error2');
} elseif ('generate' == $func) {
    // generate all articles,cats,templates,caches
    $alertSuccess = rex_delete_cache();
} elseif ('updateassets' == $func) {
    rex_dir::copy(rex_path::core('assets'), rex_path::coreAssets());
    rex_dir::copy(rex_path::core('node_modules/@shoelace-style/shoelace'), rex_path::coreAssets('shoelace'));
    $alertSuccess = 'Updated assets';
} elseif ('debugmode' == $func) {
    $configFile = rex_path::coreData('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile),
    );

    if (!is_array($config['debug'])) {
        $config['debug'] = [];
    }

    $config['debug']['enabled'] = !rex::isDebugMode();
    rex::setProperty('debug', $config['debug']);
    if (rex_file::putConfig($configFile, $config) > 0) {
        // reload the page so that debug mode is immediately visible
        rex_response::sendRedirect(rex_url::currentBackendPage(['rex_debug_updated' => true], false));
    }
} elseif ('updateinfos' == $func) {
    $configFile = rex_path::coreData('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile),
    );

    $settings = rex_post('settings', 'array', []);

    foreach (['server', 'servernam1e', 'error_email', 'lang'] as $key) {
        if (!isset($settings[$key]) || !$settings[$key]) {
            $alertError[] = rex_i18n::msg($key . '_required');
            continue;
        }
        $config[$key] = $settings[$key];
        try {
            rex::setProperty($key, $settings[$key]);
        } catch (InvalidArgumentException) {
            $alertError[] = rex_i18n::msg($key . '_invalid');
        }
    }

    foreach (rex_system_setting::getAll() as $setting) {
        $key = $setting->getKey();
        if (isset($settings[$key])) {
            if (true !== ($msg = $setting->setValue($settings[$key]))) {
                $alertError[] = $msg;
            }
        }
    }

    if (empty($alertError)) {
        if (rex_file::putConfig($configFile, $config) > 0) {
            $alertSuccess = rex_i18n::msg('info_updated');
        }
    }
} elseif ('update_editor' === $func) {
    $editor = rex_post('editor', [
        ['name', 'string', null],
        ['basepath', 'string', null],
        ['update_cookie', 'bool', false],
        ['delete_cookie', 'bool', false],
    ]);

    $editor['name'] = $editor['name'] ?: null;
    $editor['basepath'] = $editor['basepath'] ?: null;

    $cookieOptions = ['samesite' => 'strict'];

    if ($editor['delete_cookie']) {
        rex_response::clearCookie('editor', $cookieOptions);
        rex_response::clearCookie('editor_basepath', $cookieOptions);
        unset($_COOKIE['editor']);
        unset($_COOKIE['editor_basepath']);

        $alertSuccess = rex_i18n::msg('system_editor_success_cookie_deleted');
    } elseif ($editor['update_cookie']) {
        rex_response::sendCookie('editor', $editor['name'], $cookieOptions);
        rex_response::sendCookie('editor_basepath', $editor['basepath'], $cookieOptions);
        $_COOKIE['editor'] = $editor['name'];
        $_COOKIE['editor_basepath'] = $editor['basepath'];

        $alertSuccess = rex_i18n::msg('system_editor_success_cookie');
    } else {
        $configFile = rex_path::coreData('config.yml');
        $config = rex_file::getConfig($configFile);

        $config['editor'] = $editor['name'];
        $config['editor_basepath'] = $editor['basepath'];
        rex::setProperty('editor', $config['editor']);
        rex::setProperty('editor_basepath', $config['editor_basepath']);

        rex_file::putConfig($configFile, $config);
        $alertSuccess = rex_i18n::msg('system_editor_success_configyml');
    }
}

$dbConfig = rex::getDbConfig(1);

$rexVersion = rex::getVersion();
if (str_contains($rexVersion, '-dev')) {
    $hash = rex_version::gitHash(rex_path::base(), 'redaxo/redaxo');
    if ($hash) {
        $rexVersion .= '#'. $hash;
    }
}

$content = '';
foreach (rex_system_setting::getAll() as $setting) {
    $field = $setting->getField();
    if (!($field instanceof rex_form_element)) {
        throw new rex_exception($setting::class . '::getField() must return a rex_form_element!');
    }
    $field->setAttribute('name', 'settings[' . $setting->getKey() . ']');
    $content .= $field->get();
}


$editor = rex_editor::factory();
$configYml = rex_path::coreData('config.yml');

$locales = rex_i18n::getLocales();
asort($locales);
$langChoices = [];
foreach ($locales as $locale) {
    $langChoices[rex_i18n::msgInLocale('lang', $locale).' ('.$locale.')'] = $locale;
}

$viaCookie = array_key_exists('editor', $_COOKIE);

$sql = rex_sql::factory();
?>

<?php if (!empty($alertError)): ?>
    <?= (new Error(
        slotDefault: new Slot($alertError)
    ))->render() ?>
<?php endif ?>

<?php if ('' != $alertSuccess): ?>
    <?= (new Success(
        slotDefault: new Slot($alertSuccess)
    ))->render() ?>
<?php endif ?>

<div class="row">
    <div class="col-lg-8">
        <form id="rex-form-system-setup" action="<?= rex_url::currentBackendPage() ?>" method="post">
            <input type="hidden" name="func" value="updateinfos" />
            <?= $csrfToken->getHiddenField() ?>

            <?= (new Card(
                slotDefault: new Slot(static function() use($langChoices, $editor, $configYml) { ?>
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
                        choices: $langChoices,
                        required: true,
                    ))->render() ?>

                    <?= (new Input(
                        label: rex_i18n::msg('error_email'),
                        name: 'settings[error_email]',
                        value: rex::getErrorEmail(),
                        required: true,
                    ))->render() ?>

                    <?php if ($url = $editor->getUrl($configYml, 0)): ?>
                        <?= (new Button(
                            slotDefault: new Slot(rex_i18n::msg('system_editor_open_file', rex_path::basename($configYml))),
                            href: $url,
                            variant: ButtonVariant::Primary,
                            size: ButtonSize::Small,
                        ))->render() ?>

                        <p>
                            <?= rex_i18n::msg('system_edit_config_note') ?>
                        </p>
                    <?php endif ?>
                <?php }),
                slotHeader: new Slot(static function() { ?>
                    <div>
                        <?= rex_i18n::msg('system_settings') ?>
                    </div>
                <?php }),
                slotFooter: new Slot(static function() { ?>
                    <?= (new Button\Save(
                        name: 'sendit',
                    ))->render() ?>
                <?php })
            ))->render() ?>
        </form>

        <form id="rex-form-system-setup" action="<?= rex_url::currentBackendPage() ?>" method="post">
            <input type="hidden" name="func" value="update_editor" />
            <?= $csrfToken->getHiddenField() ?>
            <?= (new Card(
                slotDefault: new Slot(static function() use($viaCookie, $editor) { ?>
                    <p><?= rex_i18n::msg('system_editor_note') ?></p>

                    <?= (new Choice(
                        label: rex_i18n::msg('system_editor_name'),
                        name: 'editor[name]',
                        value: $editor->getName(),
                        choices: [rex_i18n::msg('system_editor_no_editor') => ''] + array_flip($editor->getSupportedEditors()),
                    ))->render() ?>

                    <?= (new Input(
                        label: rex_i18n::msg('system_editor_basepath'),
                        name: 'editor[basepath]',
                        value: rex_escape($editor->getBasepath()),
                        notice: rex_i18n::msg('system_editor_basepath_note'),
                    ))->render() ?>

                    <?= ($viaCookie ? (new Info(
                        slotDefault: new Slot(rex_i18n::msg('system_editor_note_cookie')),
                    ))->render() : '') ?>
                <?php }),

                slotHeader: new Slot(static function() { ?>
                    <div>
                        <?= rex_i18n::msg('system_editor') ?>
                    </div>
                <?php }),

                slotFooter: new Slot(static function() use($viaCookie) { ?>
                    <div>
                        <?= (new Button\Save(
                            slotDefault: new Slot(rex_i18n::msg('system_editor_update_cookie')),
                            name: 'editor[update_cookie]',
                            value: '1',
                        ))->render() ?>

                        <?php if ($viaCookie): ?>
                            <?= (new Button(
                                slotDefault: new Slot(rex_i18n::msg('system_editor_delete_cookie')),
                                variant: ButtonVariant::Danger,
                                type: ButtonType::Submit,
                                name: 'editor[delete_cookie]',
                                value: '1',
                            ))->render() ?>
                        <?php else: ?>
                            <?= (new Button\Save(
                                slotDefault: new Slot(rex_i18n::msg('system_editor_update_configyml')),
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
            slotDefault: new Slot(static function() use($csrfToken) { ?>
                <h3><?= rex_i18n::msg('delete_cache') ?></h3>
                <p><?= rex_i18n::msg('delete_cache_description') ?></p>
                <p>
                    <?= (new Button(
                        slotDefault: new Slot(rex_i18n::msg('delete_cache')),
                        href: (rex_url::currentBackendPage(['func' => 'generate'] + $csrfToken->getUrlParams())),
                        variant: ButtonVariant::Danger,
                    ))->render() ?>
                </p>

                <h3><?= rex_i18n::msg('debug_mode') ?></h3>
                <p><?= rex_i18n::msg('debug_mode_note') ?></p>
                <p>
                    <?= (new Button(
                        slotDefault: new Slot(rex::isDebugMode() ? rex_i18n::msg('debug_mode_off') : rex_i18n::msg('debug_mode_on')),
                        slotPrefix: new Slot(static function() { ?>
                            <?= (new Icon(
                                name: IconLibrary::Debug,
                            ))->render() ?>
                            <?php }),
                        href: (rex_url::currentBackendPage(['func' => 'debugmode'] + $csrfToken->getUrlParams())),
                        variant: ButtonVariant::Warning,
                        attributes: ['data-pjax' => 'false'] + (!rex::isDebugMode() ? ['data-confirm' => rex_i18n::msg('debug_confirm')] : []),
                    ))->render() ?>
                </p>

                <h3><?= rex_i18n::msg('safemode') ?></h3>
                <p><?= rex_i18n::msg('safemode_text') ?></p>
                <p>
                    <?= (new Button(
                        slotDefault: new Slot(static function() { ?>
                            <?php if (rex::isSafeMode()): ?>
                                <?= rex_i18n::msg('safemode_deactivate') ?>
                            <?php else: ?>
                                <?= rex_i18n::msg('safemode_activate') ?>
                            <?php endif ?>
                        <?php }),
                        href: rex_url::currentBackendPage(['safemode' => (rex::isSafeMode() ? '0' : '1')] + $csrfToken->getUrlParams()),
                        variant: ButtonVariant::Warning,
                        attributes: [
                            'data-pjax' => 'false',
                        ],
                    ))->render() ?>
                </p>

                <h3><?= rex_i18n::msg('setup') ?></h3>
                <p><?= rex_i18n::msg('setup_text') ?></p>
                <p>
                    <?= (new Button(
                        slotDefault: new Slot(rex_i18n::msg('setup')),
                        href: rex_url::currentBackendPage(['func' => 'setup'] + $csrfToken->getUrlParams()),
                        variant: ButtonVariant::Primary,
                        attributes: [
                            'data-pjax' => 'false',
                            'data-confirm' => rex_i18n::msg('setup_restart'),
                        ],
                    ))->render() ?>
                </p>

        <?php }),
            slotHeader: new Slot('<div>'.rex_i18n::msg('system_features').'</div>'),
        ))->render() ?>

        <?= (new Card(
            slotDefault: new Slot(static function() use($rexVersion) { ?>
                <table class="table">
                    <tr>
                        <th class="rex-table-width-3">REDAXO</th>
                        <td>
                            <?php if (rex_version::isUnstable($rexVersion)): ?>
                                <?= (new Icon(
                                    // @Todo rex-icon-unstable-version
                                    name: IconLibrary::Debug,
                                    label: rex_i18n::msg('unstable_version'),
                                ))->render() ?>
                            <?php endif ?>
                            <?= rex_escape($rexVersion) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>PHP</th>
                        <td>
                            <?= (new Button(
                                slotDefault: new Slot(static function() { ?>
                                    <?= PHP_VERSION ?>
                                <?php }),
                                slotSuffix: new Slot(static function() { ?>
                                    <?= (new Icon(
                                        // @Todo rex-icon-phpinfo
                                        name: IconLibrary::Debug,
                                    ))->render() ?>
                                <?php }),
                                href: rex_url::backendPage('system/phpinfo'),
                                attributes: [
                                    'onclick' => 'newWindow("phpinfo", this.href, 1000,800,",status=yes,resizable=yes"); return false;',
                                ]
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
            slotHeader: new Slot(static function() { ?>
                <div>
                    <?= rex_i18n::msg('installation') ?>
                </div>
            <?php }),
        ))->render() ?>

        <?= (new Card(
            slotDefault: new Slot(static function() use($sql, $dbConfig) { ?>
                <table class="table">
                    <tr>
                        <th class="rex-table-width-3"><?= rex_i18n::msg('version') ?></th>
                        <td><?= $sql->getDbType() ?> <?= $sql->getDbVersion() ?></td>
                    </tr>
                    <tr>
                        <th><?= rex_i18n::msg('name') ?></th>
                        <td><span class="rex-word-break"><?= $dbConfig->name ?></span></td>
                    </tr>
                    <tr>
                        <th><?= rex_i18n::msg('host') ?></th>
                        <td><?= $dbConfig->host ?></td>
                    </tr>
                </table>
                <?php }),

            slotHeader: new Slot(static function() { ?>
                <div>
                    <?= rex_i18n::msg('database') ?>
                </div>
            <?php }),
        ))->render() ?>
    </div>
</div>
