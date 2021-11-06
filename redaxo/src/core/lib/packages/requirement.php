<?php

/**
 * @package redaxo\core\packages
 */
class rex_package_requirement
{
    /**
     * @var array
     */
    private $requirements;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $i18nPrefix;

    public function __construct(array $requirements, string $i18nPrefix)
    {
        $this->requirements = $requirements;
        $this->i18nPrefix = $i18nPrefix;
    }

    /**
     * Translates the given key.
     *
     * @param string $key Key
     *
     * @return string Tranlates text
     */
    protected function i18n($key)
    {
        $args = func_get_args();
        $key = $this->i18nPrefix . $args[0];
        if (!rex_i18n::hasMsg($key)) {
            $key = 'package_' . $args[0];
        }
        $args[0] = $key;

        return call_user_func_array([rex_i18n::class, 'msg'], $args);
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Checks whether the redaxo requirement is met.
     *
     * @param string $redaxoVersion REDAXO version
     *
     * @return bool
     */
    public function checkRedaxoRequirement($redaxoVersion)
    {
        if (isset($this->requirements['redaxo']) && !rex_version::matchVersionConstraints($redaxoVersion,$this->requirements['redaxo'])) {
            $this->message = $this->i18n('requirement_error_redaxo_version', $redaxoVersion, $this->requirements['redaxo']);
            return false;
        }
        return true;
    }

    /**
     * Checks whether the package requirement is met.
     *
     * @param string $packageId Package ID
     *
     * @return bool
     */
    public function checkPackageRequirement($packageId)
    {
        $requirements = $this->requirements;

        if (!isset($requirements['packages'][$packageId])) {
            return true;
        }
        $package = rex_package::get($packageId);
        $requiredVersion = '';
        if (!$package->isAvailable()) {
            if ('' != $requirements['packages'][$packageId]) {
                $requiredVersion = ' '.$requirements['packages'][$packageId];
            }

            if (!rex_package::exists($packageId)) {
                [$addonId] = rex_package::splitId($packageId);
                $jumpToInstaller = '';
                if (rex_addon::get('install')->isAvailable() && !rex_addon::exists($addonId)) {
                    // package need to be downloaded via installer
                    $installUrl = rex_url::backendPage('install/packages/add', ['addonkey' => $addonId]);

                    $jumpToInstaller = ' <a href="'. $installUrl .'"><i class="rex-icon fa-arrow-circle-right" title="'. $this->i18n('search_in_installer', $addonId) .'"></i></a>';
                }

                $this->message = $this->i18n('requirement_error_' . $package->getType(), $packageId.$requiredVersion).$jumpToInstaller;
                return false;
            }

            // this package requires a plugin from another addon.
            // first make sure the addon itself is available.
            $jumpPackageId = $packageId;
            if ($package instanceof rex_plugin_interface && !$package->getAddon()->isAvailable()) {
                $jumpPackageId = (string) $package->getAddon()->getPackageId();
            }

            $jumpPackageUrl = '#package-'.  rex_string::normalize($jumpPackageId, '-', '_');
            if ('packages' !== rex_be_controller::getCurrentPage()) {
                // error while update/install within install-addon. x-link to packages core page
                $jumpPackageUrl = rex_url::backendPage('packages').$jumpPackageUrl;
            }

            $this->message = $this->i18n('requirement_error_' . $package->getType(), $packageId.$requiredVersion) . ' <a href="'. $jumpPackageUrl .'"><i class="rex-icon fa-arrow-circle-right" title="'. $this->i18n('jump_to', $jumpPackageId) .'"></i></a>';
            return false;
        }

        if (!rex_version::matchVersionConstraints($package->getVersion(), $requirements['packages'][$packageId])) {
            $this->message = $this->i18n(
                'requirement_error_' . $package->getType() . '_version',
                $package->getPackageId(),
                $package->getVersion(),
                $requirements['packages'][$packageId]
            );
            return false;
        }
        return true;
    }
}
