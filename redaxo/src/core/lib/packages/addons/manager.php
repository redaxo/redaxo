<?php

/**
 * @extends rex_package_manager<rex_addon>
 *
 * @package redaxo\core\packages
 */
class rex_addon_manager extends rex_package_manager
{
    /**
     * @param rex_addon $addon Addon
     */
    protected function __construct(rex_addon $addon)
    {
        parent::__construct($addon, 'addon_');
    }

    public function install($installDump = true)
    {
        $this->generatePackageOrder = false;
        $return = parent::install($installDump);
        $this->generatePackageOrder = true;

        if ($return) {
            self::generatePackageOrder();
        }

        return $return;
    }

    public function uninstall($installDump = true)
    {
        $isActivated = $this->package->isAvailable();
        if ($isActivated && !$this->deactivate()) {
            return false;
        }

        return parent::uninstall($installDump);
    }

    public function activate()
    {
        $this->generatePackageOrder = false;
        $state = parent::activate();
        $this->generatePackageOrder = true;

        if (!$state) {
            return false;
        }

        self::generatePackageOrder();

        return true;
    }

    protected function wrongPackageId($addonName)
    {
        return $this->i18n('wrong_dir_name', $addonName);
    }
}
