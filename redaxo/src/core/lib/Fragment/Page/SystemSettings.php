<?php

namespace Redaxo\Core\Fragment\Page;

use InvalidArgumentException;
use Redaxo\Core\Fragment\Page;
use rex;
use rex_config_db;
use rex_csrf_token;
use rex_dir;
use rex_editor;
use rex_exception;
use rex_file;
use rex_form_element;
use rex_i18n;
use rex_path;
use rex_response;
use rex_setup;
use rex_sql;
use rex_system_setting;
use rex_url;
use rex_version;

/**
 * @see redaxo/src/core/fragments/core/page/SystemSettings.php
 */
final class SystemSettings extends Page
{
    public readonly rex_csrf_token $csrfToken;
    public readonly string $rexVersion;
    public readonly rex_sql $sql;
    public readonly rex_config_db $dbConfig;
    public readonly string $configYml;
    public readonly rex_editor $editor;
    public readonly bool $editorViaCookie;
    public ?string $success = null;
    /** @var list<string> */
    public array $errors = [];

    public function __construct()
    {
        $this->csrfToken = rex_csrf_token::factory('system');

        $func = rex_request('func', 'string');

        if (rex_request('rex_debug_updated', 'bool', false)) {
            $this->success = (rex::isDebugMode()) ? rex_i18n::msg('debug_mode_info_on') : rex_i18n::msg('debug_mode_info_off');
        }

        if ($func && !$this->csrfToken->isValid()) {
            $this->errors[] = rex_i18n::msg('csrf_token_invalid');
        } elseif ('setup' == $func) {
            // REACTIVATE SETUP
            if (false !== $url = rex_setup::startWithToken()) {
                header('Location:' . $url);
                exit;
            }
            $this->errors[] = rex_i18n::msg('setup_error2');
        } elseif ('generate' == $func) {
            // generate all articles,cats,templates,caches
            $this->success = rex_delete_cache();
        } elseif ('updateassets' == $func) {
            rex_dir::copy(rex_path::core('assets'), rex_path::coreAssets());
            rex_dir::copy(rex_path::core('node_modules/@shoelace-style/shoelace'), rex_path::coreAssets('shoelace'));
            $this->success = 'Updated assets';
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
                rex_response::sendRedirect(rex_url::currentBackendPage(['rex_debug_updated' => true]));
            }
        } elseif ('updateinfos' == $func) {
            $configFile = rex_path::coreData('config.yml');
            $config = array_merge(
                rex_file::getConfig(rex_path::core('default.config.yml')),
                rex_file::getConfig($configFile),
            );

            $settings = rex_post('settings', 'array', []);

            foreach (['server', 'servername', 'error_email', 'lang'] as $key) {
                if (!isset($settings[$key]) || !$settings[$key]) {
                    $this->errors[] = rex_i18n::msg($key . '_required');
                    continue;
                }
                $config[$key] = $settings[$key];
                try {
                    rex::setProperty($key, $settings[$key]);
                } catch (InvalidArgumentException) {
                    $this->errors[] = rex_i18n::msg($key . '_invalid');
                }
            }

            foreach (rex_system_setting::getAll() as $setting) {
                $key = $setting->getKey();
                if (isset($settings[$key])) {
                    if (true !== ($msg = $setting->setValue($settings[$key]))) {
                        $this->errors[] = $msg;
                    }
                }
            }

            if (empty($this->errors)) {
                if (rex_file::putConfig($configFile, $config) > 0) {
                    $this->success = rex_i18n::msg('info_updated');
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

                $this->success = rex_i18n::msg('system_editor_success_cookie_deleted');
            } elseif ($editor['update_cookie']) {
                rex_response::sendCookie('editor', $editor['name'], $cookieOptions);
                rex_response::sendCookie('editor_basepath', $editor['basepath'], $cookieOptions);
                $_COOKIE['editor'] = $editor['name'];
                $_COOKIE['editor_basepath'] = $editor['basepath'];

                $this->success = rex_i18n::msg('system_editor_success_cookie');
            } else {
                $configFile = rex_path::coreData('config.yml');
                $config = rex_file::getConfig($configFile);

                $config['editor'] = $editor['name'];
                $config['editor_basepath'] = $editor['basepath'];
                rex::setProperty('editor', $config['editor']);
                rex::setProperty('editor_basepath', $config['editor_basepath']);

                rex_file::putConfig($configFile, $config);
                $this->success = rex_i18n::msg('system_editor_success_configyml');
            }
        }

        $this->sql = rex_sql::factory();
        $this->dbConfig = rex::getDbConfig();

        $rexVersion = rex::getVersion();
        if (str_contains($rexVersion, '-dev')) {
            $hash = rex_version::gitHash(rex_path::base(), 'redaxo/redaxo');
            if ($hash) {
                $rexVersion .= '#'. $hash;
            }
        }
        $this->rexVersion = $rexVersion;

        $this->configYml = rex_path::coreData('config.yml');
        $this->editor = rex_editor::factory();
        $this->editorViaCookie = array_key_exists('editor', $_COOKIE);
    }

    /** @return array<string, string> */
    public function getLangChoices(): array
    {
        $locales = rex_i18n::getLocales();
        asort($locales);

        $langChoices = [];
        foreach ($locales as $locale) {
            $langChoices[rex_i18n::msgInLocale('lang', $locale).' ('.$locale.')'] = $locale;
        }

        return $langChoices;
    }

    /** @return iterable<int, rex_form_element> */
    public function getSystemSettings(): iterable
    {
        foreach (rex_system_setting::getAll() as $setting) {
            $field = $setting->getField();
            if (!$field instanceof rex_form_element) {
                throw new rex_exception($setting::class . '::getField() must return a rex_form_element!');
            }
            $field->setAttribute('name', 'settings[' . $setting->getKey() . ']');

            yield $field;
        }
    }

    protected function getPath(): string
    {
        return 'core/page/SystemSettings.php';
    }
}
